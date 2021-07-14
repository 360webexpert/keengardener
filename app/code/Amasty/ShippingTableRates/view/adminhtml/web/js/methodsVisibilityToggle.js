require(
    ['jquery', 'domReady!'],
    function ($) {
        var amRateMethods = $('#amastrate_methods'),
            amRatesImport = 'amstrates_rate_import',
            magentoImport = 'adminhtml-import-index',
            imagesPathField = $('.field-import_images_file_dir');

        if ($('body').hasClass(magentoImport)) {
            amRateMethods.hide().find('select').removeClass('required-entry');
        } else {
            imagesPathField.hide();
        }

        $(document).on('change', '#entity', function () {
            if (this.value === amRatesImport) {
                amRateMethods.show().find('select').addClass('required-entry');
                imagesPathField.hide();
            } else {
                amRateMethods.hide().find('select').removeClass('required-entry');
                imagesPathField.show();
            }
        });
    }
);
