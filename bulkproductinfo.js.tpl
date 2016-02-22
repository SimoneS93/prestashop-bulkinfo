var data = {$bulkproductsinfo|json_encode};
var hotElement = document.getElementById('hot');
var hot = new Handsontable(hotElement, {
    data: data,
    columns: [
        {
            data: "id_product",
            type: "numeric",
            width: 40
        },
        {
            data: "name",
            type: "text"
        },
        {
            data: "reference",
            type: "text"
        },
        {
            data: "upc",
            type: "text"
        },
        {
            data: "ean13",
            type: "text"
        },
        {
            data: "unity",
            type: "text"
        },
        {
            data: "minimal_quantity",
            type: "numeric",
            format: "0"
        },
        {
            data: "width",
            type: "numeric",
            format: "0.000"
        },
        {
            data: "height",
            type: "numeric",
            format: "0.000"
        },
        {
            data: "depth",
            type: "numeric",
            format: "0.000"
        },
        {
            data: "weight",
            type: "numeric",
            format: "0.000"
        },
    ],
    stretchH: "all",
    width: '100%',
    height: 700
    autoWrapRow: true,
    maxRows: 10000,
    columnSorting: true,
    sortIndicator: true,
    autoColumnSize: {    
        "samplingRatio": 23
    },
    rowHeaders: true,
    colHeaders: [    
        "#",
        "{l s='Product'}",
        "{l s='Reference'}",
        "{l s='UPC'}",
        "{l s='EAN13'}",
        "{l s='Unity'}",
        "{l s='Minimal qty'}",
        "{l s='Width'}",
        "{l s='Height'}",
        "{l s='Depth'}",
        "{l s='Weight'}"
    ],
    manualRowResize: true,
    manualColumnResize: true,
});


/**
 * Save updated values
 */
 $('form.bulkproductinfo').on('submit', function() {
 	$(this).find('[name=bulkproductinfo_data]').val(JSON.stringify(data));
 });