import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const endpointOptions = [
	{
		label: __( 'Imprint', 'rrze-legal' ),
		value: 'imprint',
	},
	{
		label: __( 'Privacy Policy', 'rrze-legal' ),
		value: 'privacy',
	},
	{
		label: __( 'Accessibility Statement', 'rrze-legal' ),
		value: 'accessibility',
	},
];

function getEndpointLabel( endpoint ) {
	const option = endpointOptions.find( ( { value } ) => value === endpoint );

	return option ? option.label : endpointOptions[ 0 ].label;
}

export default function Edit( { attributes, setAttributes } ) {
	const endpoint = attributes.endpoint || 'imprint';
	const blockProps = useBlockProps( {
		className: 'rrze-legal',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Legal text', 'rrze-legal' ) }>
					<SelectControl
						label={ __( 'Content', 'rrze-legal' ) }
						value={ endpoint }
						options={ endpointOptions }
						onChange={ ( value ) =>
							setAttributes( { endpoint: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<strong>{ __( 'RRZE Legal', 'rrze-legal' ) }</strong>
				<p>{ getEndpointLabel( endpoint ) }</p>
				<p>
					{ __(
						'The legal text is generated dynamically from the RRZE Legal settings.',
						'rrze-legal'
					) }
				</p>
			</div>
		</>
	);
}
