/**
 * Shared Tabulator defaults for the FHOA app.
 * Every page-level Tabulator instance should spread these options
 * and override only what it needs.
 *
 * Usage:
 *   var table = new Tabulator("#my-div", Object.assign({}, FHOA_TABLE_DEFAULTS, {
 *       columns: [...],
 *   }));
 */
window.FHOA_TABLE_DEFAULTS = {
    layout: "fitDataFill",
    pagination: true,
    paginationSize: 25,
    paginationSizeSelector: [10, 25, 50, 100],
    paginationCounter: "rows",
    movableColumns: false,
    resizableRows: false,
    placeholder: "No records found",
    locale: true,
    langs: {
        "default": {
            "pagination": {
                "first": "«",
                "first_title": "First",
                "last": "»",
                "last_title": "Last",
                "prev": "‹",
                "prev_title": "Previous",
                "next": "›",
                "next_title": "Next",
                "all": "All",
                "counter": {
                    "showing": "Showing",
                    "of": "of",
                    "rows": "rows",
                    "pages": "pages",
                }
            }
        }
    }
};
