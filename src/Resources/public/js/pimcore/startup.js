pimcore.registerNS("pimcore.plugin.TorqITPortableClassificationStoreBundle");

function exportClassificationStore (exportForm) {
  console.log(123);
  if (!exportForm.isValid()) {
    return;
  }
}

function getJsonStoreForClassificationStores() {
  return new Ext.data.JsonStore({
    autoLoad: true,
    forceSelection: true,
    autoDestroy: true,
    proxy: {
      type: "ajax",
      url: "/admin/classificationstore/storetree",
      extraParams: { classId: this.classId },
    },
    fields: ["id", "text"],
  });
}

Ext.define("pimcore.plugin.TorqITPortableClassificationStoreBundle", {
  override: "pimcore.object.classificationstore.storeTree",
  parentGetTabPanel: pimcore.object.classificationstore.storeTree.prototype.getTabPanel,
  parentGetStore: pimcore.object.classificationstore.storeTree.prototype.getStoreTree,
  importRoute: Routing.generate('pimcore_bundle_portalclassificationstore_upload'),
  initialize: function () {
    const tabPanel = this.parentGetTabPanel();
    console.log("asdf");
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
    return new Ext.Button({
      tooltip: t("import"),
      iconCls: "pimcore_icon_upload",
      handler: function () {
        pimcore.helpers.uploadDialog(
          this.importRoute,
          "Filedata",
          function (response) {
            response = response.response;
            const data = Ext.decode(response.responseText);

            if (data && data.success) {
              this.parentGetStore().reload();
            } else {
              alert(data.message);
            }
          }.bind(this),
          function (response) {
            response = response.response;
            const data = Ext.decode(response.responseText);
            Ext.MessageBox.alert(t("error"), data.message);
          }
        );
      }.bind(this),
    });
  },

  getExportButton: function () {
    return {
      text: t("export"),
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
          title: 'Select an Option',
          width: 700,
          layout: 'fit',
          modal: true, // Make the window modal
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
                    exportModal.close(); // Close the popup without doing anything
                  }
              }
          ]
        });

        exportModal.show();
      },
      iconCls: "pimcore_icon_add",
    };
  },
});


