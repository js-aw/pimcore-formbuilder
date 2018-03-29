pimcore.registerNS('Formbuilder.comp.conditionalLogic.action');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.action.abstract');
Formbuilder.comp.conditionalLogic.action.abstract = Class.create({

    panel: null,

    data: null,

    sectionId: 0,

    index: 0,

    fieldConfiguration: {},

    initialize: function (panel, data, sectionId, index, fieldConfiguration) {
        this.panel = panel;
        this.data = data;
        this.sectionId = sectionId;
        this.index = index;
        this.fieldConfiguration = fieldConfiguration;
    },

    getItem: function () {
        return [];
    },

    generateFieldName: function (sectionId, index, name) {
        return 'cl.' + sectionId + '.action.' + index + '.' + name;
    },

    getTopBar: function (index) {
        var _ = this;
        return [
            {
                iconCls: this.fieldConfiguration.icon,
                disabled: true
            },
            {
                xtype: 'tbtext',
                text: "<b>" + t(this.fieldConfiguration.name) + "</b>"
            },
            '->',
            {
                iconCls: 'pimcore_icon_delete',
                handler: function (index, parent) {
                    _.panel.removeField('action', index);
                }.bind(window, index, parent)
            }];
    },
});
