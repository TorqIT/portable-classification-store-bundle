pimcore.registerNS("pimcore.plugin.TorqITPortableClassificationStoreBundle");

Ext.define("pimcore.plugin.TorqITPortableClassificationStoreBundle", {
  override: "pimcore.object.classificationstore.storeTree",
  parentGetTabPanel:
    pimcore.object.classificationstore.storeTree.prototype.getTabPanel,
  parentGetStore:
    pimcore.object.classificationstore.storeTree.prototype.getStoreTree,
  importRoute: "/admin/portable-classification-store/import",
  exportRoute: "/admin/portable-classification-store/export",
  initialize: function () {
    const tabPanel = this.parentGetTabPanel();

    tabPanel.addDocked(
      [
        { xtype: "tbfill" },
        this.getImportButton(),
        { xtype: "tbfill" },
        this.getExportButton(),
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
      handler: this.onExport.bind(this),
      iconCls: "pimcore_icon_add",
    };
  },

  onExport: function () {},
});
