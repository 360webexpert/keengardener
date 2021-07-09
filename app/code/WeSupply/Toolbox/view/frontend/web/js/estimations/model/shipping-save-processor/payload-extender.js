define(['jquery'], function ($) {
    'use strict';

    return function (payload) {
        var selectedDT = $('#selected_delivery_timestamp_' +
                payload['addressInformation']['shipping_method_code'] + '_' +
                payload['addressInformation']['shipping_carrier_code']
            );

        if (selectedDT.length) {
            payload.addressInformation['extension_attributes'] = {
                'selected_delivery_timestamp': selectedDT.val()
            };
        }

        return payload;
    };
});
