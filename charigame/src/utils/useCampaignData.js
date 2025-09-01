/**
 * Utility functions for fetching campaign and recipient data
 */
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Custom hook to fetch campaign and recipient data based on current post context
 *
 * @param {Object} options - Configuration options
 * @param {Function} options.onSuccess - Callback when recipients are successfully loaded
 * @param {Function} options.onError - Callback when an error occurs
 * @param {Function} options.onLoadingChange - Callback when loading state changes
 * @returns {Object} - Campaign and recipient data with loading state
 */
export const useCampaignData = (options = {}) => {
  const { onSuccess, onError, onLoadingChange } = options;

  const [campaignId, setCampaignId] = useState(null);
  const [recipientsData, setRecipientsData] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);

  // Get current post ID from the editor
  const currentPostId = useSelect((select) => select('core/editor')?.getCurrentPostId(), []);
  const currentPostType = useSelect((select) => select('core/editor')?.getCurrentPostType(), []);

  // Set loading state and notify via callback if provided
  const updateLoadingState = (loading) => {
    setIsLoading(loading);
    if (onLoadingChange) {
      onLoadingChange(loading);
    }
  };

  // Handle errors and notify via callback if provided
  const handleError = (err) => {
    setError(err);
    updateLoadingState(false);
    if (onError) {
      onError(err);
    }
  };

  // Function to fetch recipient data for a given campaign ID
  const fetchRecipients = async (campaignId) => {
    try {
      // Get the campaign with its recipient field
      const campaign = await apiFetch({
        path: `/wp/v2/charigame-campaign/${campaignId}?_fields=id,recipients`,
      });

      // Extract recipient IDs from the campaign data
      const recipientIds = campaign.recipients?.[0]?.recipient?.map(r => r.id) || [];

      if (!recipientIds.length) {
        console.warn('Keine Recipients in Campaign gefunden');
        updateLoadingState(false);
        return;
      }

      // Fetch recipient data for each ID
      const recipientPosts = await Promise.all(
        recipientIds.map(id =>
          apiFetch({ path: `/wp/v2/charigame-recipients/${id}` }).catch(() => null)
        )
      );

      const validRecipients = recipientPosts.filter(Boolean);

      // Get image URLs
      const imageIds = validRecipients
        .map(rec => rec.recipient_logo)
        .filter(Boolean);

      const images = await Promise.all(
        imageIds.map(id =>
          apiFetch({ path: `/wp/v2/media/${id}` }).catch(() => null)
        )
      );

      const imageMap = {};
      images.forEach(img => {
        if (img && img.id) imageMap[img.id] = img.source_url;
      });

      const recipientsWithImages = validRecipients.map(recipient => ({
        ...recipient,
        recipient_logo_url: imageMap[recipient.recipient_logo] || null,
      }));

      setRecipientsData(recipientsWithImages);
      updateLoadingState(false);

      if (onSuccess) {
        onSuccess(recipientsWithImages, campaignId);
      }

    } catch (err) {
      console.error('Fehler beim Laden der Recipient-Daten:', err);
      handleError(err);
    }
  };

  useEffect(() => {
    if (!currentPostId) return;

    updateLoadingState(true);

    if (currentPostType === 'charigame-campaign') {
      setCampaignId(currentPostId);
      fetchRecipients(currentPostId);
      return;
    }

    apiFetch({
      path: `/wp/v2/charigame-campaign?linked_landing_page=${currentPostId}&_fields=id,linked_landing_page`,
    })
      .then((campaigns) => {
        // Campaign suchen, die diese Landing Page referenziert
        const currentCampaign = campaigns.find(c => {
          if (!c.linked_landing_page || !Array.isArray(c.linked_landing_page)) {
            return false;
          }
          return c.linked_landing_page.some(lp => parseInt(lp.id, 10) === currentPostId);
        });

        if (!currentCampaign) {
          console.warn('Keine Campaign gefunden, die Landing Page referenziert', currentPostId);
          updateLoadingState(false);
          return;
        }

        const campaignId = currentCampaign.id;
        setCampaignId(campaignId);
        fetchRecipients(campaignId);
      })
      .catch((error) => {
        console.error('Fehler beim Suchen der verknüpften Campaign:', error);
        handleError(error);
      });
  }, [currentPostId, currentPostType]);

  return {
    campaignId,
    recipientsData,
    isLoading,
    error
  };
};

/**
 * Function to fetch campaign and recipient data (non-hook version)
 *
 * @param {number} postId - Current post ID
 * @param {string} postType - Current post type
 * @param {Function} onSuccess - Success callback
 * @param {Function} onError - Error callback
 */
export const fetchCampaignData = async (postId, postType, onSuccess, onError) => {
  try {
    if (postType === 'charigame-campaign') {
      await fetchRecipientsForCampaign(postId, onSuccess, onError);
      return;
    }

    // Otherwise search for linked campaign
    const campaigns = await apiFetch({
      path: `/wp/v2/charigame-campaign?linked_landing_page=${postId}&_fields=id,linked_landing_page`,
    });

    // Find campaign that references this landing page
    const currentCampaign = campaigns.find(c => {
      if (!c.linked_landing_page || !Array.isArray(c.linked_landing_page)) {
        return false;
      }
      return c.linked_landing_page.some(lp => parseInt(lp.id, 10) === postId);
    });

    if (!currentCampaign) {
      console.warn('Keine Campaign gefunden, die Landing Page referenziert', postId);
      if (onError) {
        onError(new Error('No linked campaign found'));
      }
      return;
    }

    await fetchRecipientsForCampaign(currentCampaign.id, onSuccess, onError);
  } catch (error) {
    console.error('Fehler beim Laden der Campaign-Daten:', error);
    if (onError) {
      onError(error);
    }
  }
};

/**
 * Helper to fetch recipients for a specific campaign ID
 */
async function fetchRecipientsForCampaign(campaignId, onSuccess, onError) {
  try {
    // Get the campaign with its recipient field
    const campaign = await apiFetch({
      path: `/wp/v2/charigame-campaign/${campaignId}?_fields=id,recipients`,
    });

    // Extract recipient IDs
    const recipientIds = campaign.recipients?.[0]?.recipient?.map(r => r.id) || [];

    if (!recipientIds.length) {
      if (onError) {
        onError(new Error('No recipients found for campaign'));
      }
      return;
    }

    // Fetch recipient data
    const recipientPosts = await Promise.all(
      recipientIds.map(id =>
        apiFetch({ path: `/wp/v2/charigame-recipients/${id}` }).catch(() => null)
      )
    );

    const validRecipients = recipientPosts.filter(Boolean);

    // Get image URLs
    const imageIds = validRecipients
      .map(rec => rec.recipient_logo)
      .filter(Boolean);

    const images = await Promise.all(
      imageIds.map(id =>
        apiFetch({ path: `/wp/v2/media/${id}` }).catch(() => null)
      )
    );

    const imageMap = {};
    images.forEach(img => {
      if (img && img.id) imageMap[img.id] = img.source_url;
    });

    const recipientsWithImages = validRecipients.map(recipient => ({
      ...recipient,
      recipient_logo_url: imageMap[recipient.recipient_logo] || null,
    }));

    if (onSuccess) {
      onSuccess(recipientsWithImages, campaignId);
    }
  } catch (error) {
    console.error('Fehler beim Laden der Recipient-Daten:', error);
    if (onError) {
      onError(error);
    }
  }
}
