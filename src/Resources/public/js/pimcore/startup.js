pimcore.registerNS("pimcore.plugin.TorqITPortableClassificationStoreBundle");

function getJsonStoreForClassificationStores() {
  return new Ext.data.JsonStore({
    autoLoad: true,
    forceSelection: true,
    autoDestroy: true,
    proxy: {
      type: "ajax",
      url: "/admin/classificationstore/storetree",
    },
    fields: ["id", "text"],
  });
}

Ext.define("pimcore.plugin.TorqITPortableClassificationStoreBundle", {
  override: "pimcore.object.classificationstore.storeTree",
  parentGetTabPanel: pimcore.object.classificationstore.storeTree.prototype.getTabPanel,
  parentGetStore: pimcore.object.classificationstore.storeTree.prototype.getStoreTree,
  initialize: function () {
    const tabPanel = this.parentGetTabPanel();
    tabPanel.addDocked(
      [
        {
          xtype: 'toolbar',
          dock: 'right',
          items: [
            this.getImportButton(),
            this.getExportButton(),
          ]
      }
      ],
      1
    );
    tabPanel.tabPanel.updateLayout();

    pimcore.layout.refresh();
  },
  getImportButton: function () {
    return {
      text: t("import"),
      iconCls: "pimcore_icon_upload",
      handler: function() {
      const importForm = Ext.create('Ext.form.FormPanel', {
          bodyStyle: "padding:10px;",
          items: [
            {
              xtype: "combo",
              fieldLabel: "Classification Store",
              name: "classificationStore",
              store: getJsonStoreForClassificationStores(),
              allowBlank: false,
              width: 600,
              bind: "{classificationStore}",
              valueField: "id",
              displayField: "text",
            },
            {
              xtype: 'filefield',
              name: 'classificationStoreImportFile',
              fieldLabel: 'Select File',
              labelWidth: 80,
              allowBlank: false,
              anchor: '100%',
              buttonText: 'Browse...'
            }
          ]
      });

        const importModal = Ext.create('Ext.Window', {
          title: 'Import Classification Store Data',
          width: 700,
          layout: 'fit',
          modal: true,
          items: [
            importForm
          ],
          buttons: [
              {
                  text: 'Import',
                  handler: function () {
                    const importFormValues = importForm.getValues();

                    importForm.submit({
                      url: Routing.generate('pimcore_bundle_portalclassificationstore_import', {
                        classificationstoreId: importFormValues.classificationStore
                      }),
                      params: {
                        csrfToken: pimcore.settings['csrfToken']
                      },
                      waitMsg: 'Importing data...',
                      success: function() {
                        importModal.close();
                        
                        pimcore.helpers.showNotification(t("success"), "Imported successfully", "success");
                      },
                      failure: function() {
                          pimcore.helpers.showNotification(t("error"), "Error importing data", "error");
                      }
                    });
                  }
              },
              {
                  text: 'Cancel',
                  handler: function() {
                    importModal.close();
                  }
              }
          ]
        });

        importModal.show();
      },
    };
  },

  getExportButton: function () {
    return {
      text: t("export"),
      iconCls: "pimcore_icon_download",
      handler: function() {
      const exportForm = Ext.create('Ext.form.FormPanel', {
          bodyStyle: "padding:10px;",
          items: [
            {
              xtype: "combo",
              fieldLabel: "Classification Store",
              name: "classificationStore",
              store: getJsonStoreForClassificationStores(),
              allowBlank: false,
              width: 600,
              bind: "{classificationStore}",
              valueField: "id",
              displayField: "text",
            }
          ]
      });

        const exportModal = Ext.create('Ext.Window', {
          title: 'Export Classification Store Data',
          width: 700,
          layout: 'fit',
          modal: true,
          items: [
            exportForm
          ],
          buttons: [
              {
                  text: 'Export',
                  handler: function () {
                    const exportFormValues = exportForm.getValues();

                    const url = Routing.generate('pimcore_bundle_portalclassificationstore_export', {
                      classificationstoreId: exportFormValues.classificationStore
                    });
                    
                    window.open(url, '_blank');
                  }
              },
              {
                  text: 'Cancel',
                  handler: function() {
                    exportModal.close();
                  }
              }
          ]
        });

        exportModal.show();
      },
    };
  },
});


