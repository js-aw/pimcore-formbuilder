pimcore.registerNS('Formbuilder.comp.conditionalLogic.condition');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.condition.elementValue');
Formbuilder.comp.conditionalLogic.condition.elementValue = Class.create(Formbuilder.comp.conditionalLogic.condition.abstract, {

    getItem: function () {
        var _ = this;
        var conditionTypesStore = Ext.create('Ext.data.Store', {
            fields: ['label', 'value'],
            data: [{
                label: t('form_builder_element_value_type_contains'),
                value: 'contains'
            }, {
                label: t('form_builder_element_value_type_is_checked'),
                value: 'is_checked'
            }, {
                label: t('form_builder_element_value_type_is_greater'),
                value: 'is_greater'
            }, {
                label: t('form_builder_element_value_type_is_less'),
                value: 'is_less'
            }, {
                label: t('form_builder_element_value_type_is_value'),
                value: 'is_value'
            }, {
                label: t('form_builder_element_value_type_is_not_value'),
                value: 'is_not_value'
            }]
        });

        var fieldStore = Ext.create('Ext.data.Store', {
            fields: ['name', 'display_name'],
            data: this.panel.getFormFields().fields
        });

        var descriptionField = new Ext.form.Label({
            hidden:true,
            anchor: '100%',
            flex: 1,
            style: 'display:block; padding:3px; background:#f5f5f5; border:1px solid #eee; font-weight: 300; word-wrap:break-word;'
        });

        var valueField = new Ext.form.TextField({
            name: _.generateFieldName(this.sectionId, this.index, 'value'),
            fieldLabel: t('form_builder_element_value_value'),
            anchor: '100%',
            labelAlign: 'top',
            summaryDisplay: true,
            allowBlank: this.data && this.data.comparator === 'is_checked',
            disabled: this.data && this.data.comparator === 'is_checked',
            value: this.data ? this.data.value : null,
            flex: 1,
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'value');
                }
            }
        });

        var items = [{
            xtype: 'hidden',
            name: _.generateFieldName(this.sectionId, this.index, 'type'),
            value: this.fieldConfiguration.identifier,
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'type');
                }
            }
        },
        {
            xtype: 'tagfield',
            name: _.generateFieldName(this.sectionId, this.index, 'fields'),
            fieldLabel: t('form_builder_element_value_fields'),
            style: 'margin: 0 5px 0 0',
            queryDelay: 0,
            stacked: true,
            displayField: 'display_name',
            valueField: 'name',
            mode: 'local',
            labelAlign: 'top',
            store: fieldStore,
            editable: false,
            triggerAction: 'all',
            anchor: '100%',
            value: this.data ? this.data.fields : null,
            allowBlank: false,
            flex: 1,
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'fields');
                }
            }
        }, {
            xtype: 'combo',
            name: _.generateFieldName(this.sectionId, this.index, 'comparator'),
            fieldLabel: t('form_builder_element_value_type'),
            style: 'margin: 0 5px 0 0',
            queryDelay: 0,
            displayField: 'label',
            valueField: 'value',
            mode: 'local',
            labelAlign: 'top',
            store: conditionTypesStore,
            editable: true,
            triggerAction: 'all',
            anchor: '100%',
            value: this.data ? this.data.comparator : null,
            summaryDisplay: true,
            allowBlank: false,
            flex: 1,
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'comparator');
                },
                change: function (combo, value) {
                    valueField.setDisabled(value === 'is_checked');
                    valueField.allowBlank = value === 'is_checked';
                    if(value === 'is_checked') {
                        valueField.setValue('');
                        valueField.clearInvalid();
                    } else {
                        valueField.validate();
                    }

                    if(value === 'contains') {
                        descriptionField.setHidden(false);
                        descriptionField.setHtml(t('form_builder_element_value_type_contains_description'));
                    } else {
                        descriptionField.setHidden(true);
                    }
                }
            }
        },
        valueField
        ];

        var compositeField = new Ext.form.FieldContainer({
            layout: 'hbox',
            hideLabel: true,
            style: 'padding-bottom:5px;',
            items: items
        });

        var _ = this,
            myId = Ext.id(),
            item = new Ext.form.FormPanel({
                id: myId,
                type: 'combo',
                forceLayout: true,
                style: 'margin: 10px 0 0 0',
                bodyStyle: 'padding: 10px 30px 10px 30px; min-height:30px;',
                tbar: this.getTopBar(myId),
                items: [compositeField, descriptionField],
                listeners: {}
            });

        return item;
    }
});
