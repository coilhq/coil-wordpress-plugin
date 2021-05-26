/**
 * External Dependencies
 */
import classnames from 'classnames';

// Editor and Frontend Styles
import './styles/editor.scss';
import './styles/style.scss';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor || wp.editor; // wp.editor for WP < 5.2.
const { withSelect, withDispatch } = wp.data;
const { createHigherOrderComponent } = wp.compose;
const { PanelBody, RadioControl } = wp.components;
const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost; // WP >= 5.3.

// // Allow only specific blocks to use the extension attribute. - Disabled for now!
// const allowedBlocks = [
// 	'core/paragraph',
// 	'core/heading',
// 	'core/image',
// 	'core/gallery',
// 	'core/verse',
// 	'core/spacer',
// 	'core/subhead',
// 	'core/preformatted',
// 	'core/code',
// 	'core/cover',
// 	'core/group',
// 	'core/columns',
// 	'core/media-text',
// 	'core/pullquote',
// 	'core/quote',
// 	'core/button',
// 	'core/list',
// 	'core/separator',
// 	'core/text-columns',
// 	'core/video',
// 	'core/audio',
// 	'core-embed',
// 	'core-embed/youtube',
// 	'core-embed/twitter',
// 	'core-embed/facebook',
// 	'core-embed/instagram',
// 	'core-embed/wordpress',
// 	'core-embed/soundcloud',
// 	'core-embed/spotify',
// 	'core-embed/flickr',
// 	'core-embed/vimeo',
// 	'core-embed/animoto',
// 	'core-embed/cloudup',
// 	'core-embed/collegehumor',
// 	'core-embed/crowdsignal',
// 	'core-embed/dailymotion',
// 	'core-embed/hulu',
// 	'core-embed/imgur',
// 	'core-embed/issuu',
// 	'core-embed/kickstarter',
// 	'core-embed/meetup-com',
// 	'core-embed/mixcloud',
// 	'core-embed/reddit',
// 	'core-embed/reverbnation',
// 	'core-embed/screencast',
// 	'core-embed/scribd',
// 	'core-embed/slideshare',
// 	'core-embed/smugmug',
// 	'core-embed/speaker-deck',
// 	'core-embed/ted',
// 	'core-embed/tumblr',
// 	'core-embed/videopress',
// 	'core-embed/wordpress-tv',
// 	'core-embed/amazon-kindle',
// ];

// // Restrict the blocks the extension attribute can not be applied to. - Disabled for now!
// const restrictedBlocks = [
// 	'core/block',
// 	'core/freeform',
// 	'core/shortcode',
// 	'core/template',
// 	'core/nextpage',
// ];

// - Disabled for now!
// /**
//  * Is the block allowed to support monetization.
//  *
//  * @param {string} name The name of the block.
//  */
// function allowedBlockTypes( name ) {
// 	return allowedBlocks.includes( name );
// }

// - Disabled for now!
// /**
//  * Is the block not allowed to support monetization.
//  *
//  * @param {string} name The name of the block.
//  */
// function restrictedBlockTypes( name ) {
// 	return restrictedBlocks.includes( name );
// }

/**
/**
 * Adds our attributes for our monetization data and restrict to allowed blocks.
 *
 * @param  {Object} settings Settings for the block.
 * @return {Object} settings Modified settings.
 */
function addAttributes( settings ) {
	// If this block is not allowed to use the extension attribute then move on to the next. - Disabled for now!
	/*if ( ! restrictedBlockTypes( settings.name ) ) {
		return settings;
	}*/

	// Check if this block is allowed to use the extension attribute. - Disabled for now!
	/*if ( ! allowedBlockTypes( settings.name ) ) {
		return settings;
	}*/

	// Check if object exists for old Gutenberg version compatibility.
	// Set default to "always-show" for all currently added blocks and new blocks added.
	if ( typeof settings.attributes !== 'undefined' ) {
		settings.attributes = Object.assign( settings.attributes, {
			monetizeBlockDisplay: {
				type: 'string',
				default: 'always-show',
			},
		} );
	}

	return settings;
}

/**
 * Override the default edit UI to include a new block inspector control for
 * assigning monetization, if the block supports it.
 *
 * @param  {function|Component} BlockEdit Original component.
 * @return {string} Wrapped component.
 */
const monetizeBlockControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		let showInspector = false;

		const {
			// name, - Disabled for now!
			attributes,
			setAttributes,
			isSelected,
		} = props;

		const {
			monetizeBlockDisplay,
		} = attributes;

		// Fetch the post meta.
		const meta = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'meta' );

		// If the block is not supported then don't show the inspector.
		/*if ( ! allowedBlockTypes( props.name ) || ! restrictedBlockTypes( props.name ) || ! props.isSelected ) {
			return <BlockEdit { ...props } />;
		}*/

		// Only show inspector options if set for block level monetization.
		showInspector = false;
		if ( typeof meta !== 'undefined' ) {
			if ( typeof meta._coil_monetize_post_status === 'undefined' || ( typeof meta._coil_monetize_post_status !== 'undefined' && meta._coil_monetize_post_status === 'gate-tagged-blocks' ) ) {
				showInspector = true;
			}
		}

		return (
			<Fragment>
				<BlockEdit { ...props } />
				{ isSelected && showInspector &&
					<InspectorControls>
						<PanelBody
							title={ __( 'Coil Web Monetization' ) }
							initialOpen={ false }
							className="coil-panel"
						>
							<RadioControl
								selected={ monetizeBlockDisplay }
								options={
									[
										{
											label: __( 'Always Show' ),
											value: 'always-show',
										},
										{
											label: __( 'Only Show Coil Members' ),
											value: 'show-monetize-users',
										},
										{
											label: __( 'Hide for Coil Members' ),
											value: 'hide-monetize-users',
										},
									]
								}
								help={ __( 'Set the block\'s visibility.' ) }
								onChange={ ( value ) => setAttributes( { monetizeBlockDisplay: value } ) }
							/>

						</PanelBody>
					</InspectorControls>
				}
			</Fragment>
		);
	};
}, 'monetizeBlockControls' );

/**
 * Add custom element class in save element.
 *
 * @param  {Object} extraProps Additional props applied to save element.
 * @param  {Object} blockType  Block type.
 * @param  {Object} attributes Current block attributes.
 * @return {Object} extraProps Filtered props applied to save element.
 */
function applyExtraClass( extraProps, blockType, attributes ) {
	const {
		monetizeBlockDisplay,
	} = attributes;

	// If this block is not allowed to use the extension attribute then move on to the next. - Disabled for now!
	/*if ( ! restrictedBlockTypes( blockType.name ) ) {
		return extraProps;
	}*/

	// Check if this block is allowed to use the extension attribute. - Disabled for now!
	/*if ( ! allowedBlockTypes( blockType.name ) ) {
		return extraProps;
	}*/

	// Check if object exists for old Gutenberg version compatibility.
	// Add class only when monetizeBlockDisplay is set and is not always show.
	if ( typeof monetizeBlockDisplay !== 'undefined' && monetizeBlockDisplay !== 'always-show' ) {
		extraProps.className = classnames( extraProps.className, 'coil-' + monetizeBlockDisplay );
	}

	return extraProps;
}

/**
 * Adds a wrapper class around the block in the editor to identify the monetization status.
 */
const wrapperClass = createHigherOrderComponent( ( BlockListBlock ) => {
	return ( props ) => {
		let wrapperProps = props.wrapperProps;
		let customData = {};
		let allowBlockIdentity = false; // Note: Boolean value is in reverse.

		const {
			attributes,
		} = props;

		const {
			monetizeBlockDisplay,
		} = attributes;

		// Fetch the post meta.
		const meta = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'meta' );

		if ( ! meta || typeof meta._coil_monetize_post_status === 'undefined' || ( typeof meta._coil_monetize_post_status !== 'undefined' && meta._coil_monetize_post_status === 'gate-tagged-blocks' ) ) {
			allowBlockIdentity = true;
		} else {
			allowBlockIdentity = false;
		}

		customData = Object.assign( customData, {
			'data-coil-is-monetized': 1,
		} );

		wrapperProps = {
			...wrapperProps,
			...customData,
		};

		// Apply custom block wrapper class if monetization is set at the document level and block level.
		if ( typeof monetizeBlockDisplay !== 'undefined' && monetizeBlockDisplay !== 'always-show' && allowBlockIdentity ) {
			return <BlockListBlock { ...props } className={ 'coil-' + monetizeBlockDisplay } wrapperProps={ wrapperProps } />;
		}
		return <BlockListBlock { ...props } />;
	};
}, 'wrapperClass' );

// Add filters
addFilter(
	'blocks.registerBlockType',
	'coil/addAttributes',
	addAttributes
);

addFilter(
	'editor.BlockEdit',
	'coil/monetizeBlockControls',
	monetizeBlockControls
);

addFilter(
	'blocks.getSaveContent.extraProps',
	'coil/applyExtraClass',
	applyExtraClass
);

addFilter(
	'editor.BlockListBlock',
	'coil/wrapperClass',
	wrapperClass
);

// Post Monetization Fields
const PostMonetizationFields = withDispatch( ( dispatch, props ) => {
	return {
		updateMetaValue: ( value ) => {
			dispatch( 'core/editor' ).editPost( {
				meta: {
					[ props.metaFieldName ]: value,
				},
			} );
		},
	};
} )( withSelect( ( select, props ) => {
	const meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' );

	return {
		[ props.metaFieldName ]: meta && meta._coil_monetize_post_status,
	};
} )( ( props ) => (
	<RadioControl
		selected={ props[ props.metaFieldName ] ? props[ props.metaFieldName ] : 'default' }
		options={
			[
				{
					label: __( 'Default', 'coil-web-monetization' ),
					value: 'default',
				},
				{
					label: __( 'Disable Earning', 'coil-web-monetization' ),
					value: 'no',
				},
				{
					label: __( 'Earn with Public Access', 'coil-web-monetization' ),
					value: 'no-gating',
				},
				{
					label: __( 'Earn with Coil Only Access', 'coil-web-monetization' ),
					value: 'gate-all',
				},
				{
					label: __( 'Earn with Split Access', 'coil-web-monetization' ),
					value: 'gate-tagged-blocks',
				},
			]
		}
		help={ __( 'Select earning and visibility preferences for your content.' ) }
		onChange={ ( value ) => props.updateMetaValue( value ) }
	/>
) ) );

// WP >= 5.3 only - register the panel.
if ( PluginDocumentSettingPanel ) {
	registerPlugin( 'coil-document-setting-panel', {
		render: () => {
			return (
				<PluginDocumentSettingPanel
					name="coil-meta"
					title={ __( 'Coil Web Monetization', 'coil-web-monetization' ) }
					initialOpen={ false }
					className="coil-document-panel"
				>
					<PostMonetizationFields metaFieldName="_coil_monetize_post_status" />
				</PluginDocumentSettingPanel>
			);
		},
		icon: '',
	} );
}
