/**
 * BackgroundSelect Component
 *
 */

import { SelectControl } from '@wordpress/components';
import backgroundOptions from '../config/backgrounds';

/**
 * Background Select Component
 *
 * @param {Object} props Component properties
 * @param {string} props.value Aktuell ausgewählter Hintergrund-Wert
 * @param {Function} props.onChange Callback-Funktion bei Änderung der Auswahl
 * @param {string} props.label Label für das SelectControl (optional)
 * @param {string} props.help Hilfetext für das SelectControl (optional)
 * @returns {JSX.Element} SelectControl Komponente
 */
const BackgroundSelect = ({
    value,
    onChange,
    label = 'Background auswählen',
    help = ''
}) => {
    return (
        <SelectControl
            label={label}
            value={value}
            options={backgroundOptions}
            onChange={onChange}
            help={help}
        />
    );
};

export default BackgroundSelect;
