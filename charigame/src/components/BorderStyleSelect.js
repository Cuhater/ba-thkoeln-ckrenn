/**
 * BorderStyleSelect Component
 * Reusable component for selecting border styles in blocks
 */

import { SelectControl } from '@wordpress/components';
import borderStyleOptions from '../config/borderStyles';

/**
 * Border Style Select Component
 * 
 * @param {Object} props Component properties
 * @param {string} props.value Currently selected border style value
 * @param {Function} props.onChange Callback function for selection changes
 * @param {string} props.label Label for the SelectControl (optional)
 * @param {string} props.help Help text for the SelectControl (optional)
 * @returns {JSX.Element} SelectControl Component
 */
const BorderStyleSelect = ({ 
    value, 
    onChange,
    label = 'Randstil auswählen',
    help = ''
}) => {
    return (
        <SelectControl
            label={label}
            value={value}
            options={borderStyleOptions}
            onChange={onChange}
            help={help}
        />
    );
};

export default BorderStyleSelect;