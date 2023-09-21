/*
 *  Document   : be_tables_datatables.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in DataTables Page
 */

// DataTables, for more examples you can check out https://www.datatables.net/
class pageTablesDatatables {
  /*
   * Init DataTables functionality
   *
   */
  static initDataTables() {
    // Override a few default classes
    jQuery.extend(jQuery.fn.dataTable.ext.classes, {
      sWrapper: "dataTables_wrapper dt-bootstrap5",
      sFilterInput: "form-control",
      sLengthSelect: "form-select"
    });

    // Override a few defaults
    jQuery.extend(true, jQuery.fn.dataTable.defaults, {
      language: {
        lengthMenu: "_MENU_",
        search: "_INPUT_",
        searchPlaceholder: "جستجو...",
        info: "صفحه <strong>_PAGE_</strong> از <strong>_PAGES_</strong>",
        paginate: {
          first: '<i class="fa fa-angle-double-right"></i>',
          previous: '<i class="fa fa-angle-right"></i>',
          next: '<i class="fa fa-angle-left"></i>',
          last: '<i class="fa fa-angle-double-left"></i>'
        }
      }
    });

    // Override buttons default classes
    jQuery.extend(true, jQuery.fn.DataTable.Buttons.defaults, {
      dom: {
        button: {
          className: 'btn btn-sm btn-primary'
        },
      }
    });

    // Init full DataTable
    jQuery('.js-dataTable-full').DataTable({
      pageLength: 5,
      lengthMenu: [[5, 10, 20], [5, 10, 20]],
      autoWidth: false
    });

    // Init DataTable with Buttons
    jQuery('.js-dataTable-buttons').DataTable({
      pageLength: 5,
      lengthMenu: [[5, 10, 20], [5, 10, 20]],
      autoWidth: false,
      buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
      dom: "<'row'<'col-sm-12'<'text-center bg-body-light py-2 mb-2'B>>>" +
              "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });

    // Init full extra DataTable
    jQuery('.js-dataTable-full-pagination').DataTable({
      pagingType: "full_numbers",
      pageLength: 5,
      lengthMenu: [[5, 10, 20], [5, 10, 20]],
      autoWidth: false
    });

    // Init simple DataTable
    jQuery('.js-dataTable-simple').DataTable({
      pageLength: 5,
      lengthMenu: false,
      searching: false,
      autoWidth: false,
      dom: "<'row'<'col-sm-12'tr>>" +
              "<'row'<'col-sm-6'i><'col-sm-6'p>>"
    });

    // Init responsive DataTable
    jQuery('.js-dataTable-responsive').DataTable({
      pagingType: "full_numbers",
      pageLength: 5,
      lengthMenu: [[5, 10, 20], [5, 10, 20]],
      autoWidth: false,
      responsive: true
    });
  }

  /*
   * Init functionality
   *
   */
  static init() {
    this.initDataTables();
  }
}

// Initialize when page loads
Dashmix.onLoad(() => pageTablesDatatables.init());
