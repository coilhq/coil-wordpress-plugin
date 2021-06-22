!function(e){function t(o){if(n[o])return n[o].exports;var a=n[o]={i:o,l:!1,exports:{}};return e[o].call(a.exports,a,a.exports,t),a.l=!0,a.exports}var n={};t.m=e,t.c=n,t.d=function(e,n,o){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:o})},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s=0)}([function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});n(1)},function(e,t,n){"use strict";function o(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function a(e){return"undefined"!==typeof e.attributes&&(e.attributes=Object.assign(e.attributes,{monetizeBlockDisplay:{type:"string",default:"always-show"}})),e}function l(e,t,n){var o=n.monetizeBlockDisplay;return"undefined"!==typeof o&&"always-show"!==o&&(e.className=r()(e.className,"coil-"+o)),e}var i=n(2),r=n.n(i),s=n(3),u=(n.n(s),n(4)),c=(n.n(u),Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var o in n)Object.prototype.hasOwnProperty.call(n,o)&&(e[o]=n[o])}return e}),__=wp.i18n.__,p=wp.hooks.addFilter,d=wp.element.Fragment,m=wp.blockEditor||wp.editor,f=m.InspectorControls,b=wp.data,g=b.withSelect,w=b.withDispatch,y=wp.compose.createHigherOrderComponent,v=wp.components,_=v.PanelBody,z=v.RadioControl,h=v.SelectControl,E=wp.plugins.registerPlugin,P=wp.editPost.PluginDocumentSettingPanel,O=y(function(e){return function(t){var n=!1,o=t.attributes,a=t.setAttributes,l=t.isSelected,i=o.monetizeBlockDisplay,r=wp.data.select("core/editor").getEditedPostAttribute("meta");return n=!1,"undefined"!==typeof r&&("undefined"===typeof r._coil_monetize_post_status||"undefined"!==typeof r._coil_monetize_post_status&&"gate-tagged-blocks"===r._coil_monetize_post_status)&&(n=!0),wp.element.createElement(d,null,wp.element.createElement(e,t),l&&n&&wp.element.createElement(f,null,wp.element.createElement(_,{title:__("Coil Web Monetization"),initialOpen:!1,className:"coil-panel"},wp.element.createElement(z,{selected:i,options:[{label:__("Always Show"),value:"always-show"},{label:__("Only Show Paying Viewers"),value:"show-monetize-users"},{label:__("Hide For Paying Viewers"),value:"hide-monetize-users"}],help:__("Set the visibility based on the monetization you prefer."),onChange:function(e){return a({monetizeBlockDisplay:e})}}))))}},"monetizeBlockControls"),k=y(function(e){return function(t){var n=t.wrapperProps,o={},a=!1,l=t.attributes,i=l.monetizeBlockDisplay,r=wp.data.select("core/editor").getEditedPostAttribute("meta");return a=!r||"undefined"===typeof r._coil_monetize_post_status||"undefined"!==typeof r._coil_monetize_post_status&&"gate-tagged-blocks"===r._coil_monetize_post_status,o=Object.assign(o,{"data-coil-is-monetized":1}),n=Object.assign({},n,o),"undefined"!==typeof i&&"always-show"!==i&&a?wp.element.createElement(e,c({},t,{className:"coil-"+i,wrapperProps:n})):wp.element.createElement(e,t)}},"wrapperClass");p("blocks.registerBlockType","coil/addAttributes",a),p("editor.BlockEdit","coil/monetizeBlockControls",O),p("blocks.getSaveContent.extraProps","coil/applyExtraClass",l),p("editor.BlockListBlock","coil/wrapperClass",k);var S=w(function(e,t){return{updateMetaValue:function(n){e("core/editor").editPost({meta:o({},t.metaFieldName,n)})},updateSelectValue:function(e){return"gate-all"===e||"no-gating"===e||"gate-tagged-blocks"===e?"enabled":"default"===e?"default":"disabled"},updateMetaValueOnSelect:function(n){var a="no";"enabled"===n?a="gate-all":"default"===n&&(a="default"),e("core/editor").editPost({meta:o({},t.metaFieldName,a)})}}})(g(function(e,t){var n,a=e("core/editor").getEditedPostAttribute("meta"),l=__("Coil Members Only");return"no"===coilEditorParams.monetizationDefault?l=__("Disabled"):"no-gating"===coilEditorParams.monetizationDefault&&(l=__("Enabled")),n={},o(n,t.metaFieldName,a&&a._coil_monetize_post_status),o(n,"defaultLabel",l),n})(function(e){return wp.element.createElement("div",null,wp.element.createElement(h,{label:__("Select a monetization Status"),value:e.updateSelectValue(e[e.metaFieldName]),onChange:function(t){return e.updateMetaValueOnSelect(t,e)},options:[{value:"default",label:"Default ("+e.defaultLabel+")"},{value:"enabled",label:"Enabled"},{value:"disabled",label:"Disabled"}]}),wp.element.createElement("div",{className:"coil-monetization-settings "+(e[e.metaFieldName]?e[e.metaFieldName]:"")},wp.element.createElement(z,{selected:e[e.metaFieldName]?e[e.metaFieldName]:"default",options:[{label:__("No Monetization","coil-web-monetization"),value:"no"},{label:__("Use Default","coil-web-monetization"),value:"default"},{label:__("Everyone","coil-web-monetization"),value:"no-gating"},{label:__("Coil Members Only","coil-web-monetization"),value:"gate-all"},{label:__("Split","coil-web-monetization"),value:"gate-tagged-blocks"}],help:__("Set the type of monetization for the article."),onChange:function(t){return e.updateMetaValue(t)}})))}));P&&E("coil-document-setting-panel",{render:function(){return wp.element.createElement(P,{name:"coil-meta",title:__("Coil Web Monetization","coil-web-monetization"),initialOpen:!1,className:"coil-document-panel"},wp.element.createElement(S,{metaFieldName:"_coil_monetize_post_status"}))},icon:""})},function(e,t,n){var o,a;!function(){"use strict";function n(){for(var e=[],t=0;t<arguments.length;t++){var o=arguments[t];if(o){var a=typeof o;if("string"===a||"number"===a)e.push(o);else if(Array.isArray(o)){if(o.length){var i=n.apply(null,o);i&&e.push(i)}}else if("object"===a)if(o.toString===Object.prototype.toString)for(var r in o)l.call(o,r)&&o[r]&&e.push(r);else e.push(o.toString())}}return e.join(" ")}var l={}.hasOwnProperty;"undefined"!==typeof e&&e.exports?(n.default=n,e.exports=n):(o=[],void 0!==(a=function(){return n}.apply(t,o))&&(e.exports=a))}()},function(e,t){},function(e,t){}]);