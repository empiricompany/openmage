/**
 * OpenMage
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available at https://opensource.org/license/afl-3-0-php
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright   Copyright (c) 2022 The OpenMage Contributors (https://www.openmage.org)
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

tinymce.PluginManager.add('openmagevariable', (ed, url) => {
    return {        
        init: function (editor) {
            editor.addCommand('openVariablesPopup', function (commandConfig) {
                var config = tinyMceEditors.get(tinymce.activeEditor.id).openmagePluginsOptions.get('openmagevariable');
                OpenmagevariablePlugin.setEditor(editor);
                OpenmagevariablePlugin.loadChooser(
                    config.url,
                    null,
                    tinymce.activeEditor.selection.getNode()
                );
            });

            editor.ui.registry.addIcon(
                'openmagevariable',
                '<svg width="24" height="24" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">' +
                '<path d="M5.5 3C4.11929 3 3 4.11929 3 5.5V8.38197C3 8.87472 2.72331 9.32545 2.28459 9.54865C2.11627 9.62913 2 9.80099 2 10C2 10.199 2.11627 10.3709 2.28459 10.4513C2.72331 10.6745 3 11.1253 3 11.618V14.5C3 15.8807 4.11929 17 5.5 17C5.77614 17 6 16.7761 6 16.5C6 16.2239 5.77614 16 5.5 16C4.67157 16 4 15.3284 4 14.5V11.618C4 11.0029 3.75555 10.4249 3.33834 10C3.75556 9.57511 4 8.9971 4 8.38197V5.5C4 4.67157 4.67157 4 5.5 4C5.77614 4 6 3.77614 6 3.5C6 3.22386 5.77614 3 5.5 3ZM14.5 3C15.8807 3 17 4.11929 17 5.5V8.38197C17 8.87472 17.2767 9.32545 17.7154 9.54865C17.8837 9.62913 18 9.80099 18 10C18 10.199 17.8837 10.3709 17.7154 10.4513C17.2767 10.6745 17 11.1253 17 11.618V14.5C17 15.8807 15.8807 17 14.5 17C14.2239 17 14 16.7761 14 16.5C14 16.2239 14.2239 16 14.5 16C15.3284 16 16 15.3284 16 14.5V11.618C16 11.0029 16.2444 10.4249 16.6617 10C16.2444 9.57511 16 8.9971 16 8.38197V5.5C16 4.67157 15.3284 4 14.5 4C14.2239 4 14 3.77614 14 3.5C14 3.22386 14.2239 3 14.5 3ZM7.90691 6.20942C7.7464 5.98471 7.43413 5.93267 7.20942 6.09317C6.98471 6.25368 6.93267 6.56595 7.09317 6.79066L9.38559 10L7.09317 13.2094C6.93267 13.4341 6.98471 13.7464 7.20942 13.9069C7.43413 14.0674 7.7464 14.0154 7.90691 13.7907L10 10.8603L12.0932 13.7907C12.2537 14.0154 12.566 14.0674 12.7907 13.9069C13.0154 13.7464 13.0674 13.4341 12.9069 13.2094L10.6145 10L12.9069 6.79066C13.0674 6.56595 13.0154 6.25368 12.7907 6.09317C12.566 5.93267 12.2537 5.98471 12.0932 6.20942L10 9.13981L7.90691 6.20942Z"/>' +
                '</svg>'
            );
            
            editor.ui.registry.addToggleButton('openmagevariable', {
                icon: 'openmagevariable',
                tooltip: Translator.translate('Insert Variable'),
                onAction: function () {
                    editor.execCommand('openVariablesPopup');
                }
            });
        },

        getMetadata: function () {
            return {
                longname: 'OpenMage Variable Manager Plugin',
                author: 'OpenMage Core Team',
                authorurl: 'https://www.openmage.org',
                infourl: 'https://www.openmage.org',
                version: '1.0'
            };
        }
    }

});
