var config = {
    map: {
        '*': {
            wesupplyestimations: 'WeSupply_Toolbox/js/wesupplyestimations',
            iframeResizer: 'WeSupply_Toolbox/js/lib/iframeResizer.min',
            wesupplyOrderView: 'WeSupply_Toolbox/js/embedded/wesupplyOrderView',
            loadIframe: 'WeSupply_Toolbox/js/embedded/loadIframe',
            deliveryEstimate: 'WeSupply_Toolbox/js/estimations/delivery',
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': 'WeSupply_Toolbox/js/estimations/model/shipping-save-processor/payload-extender'
        }
    },
    shim: {
        wesupplyestimations: {
            deps: ['jquery']
        },
        wesupplyOrderView: {
            deps: ['jquery']
        },
        loadIframe: {
            deps: ['jquery']
        },
        iframeResizer: {
            deps: ['jquery']
        },
        deliveryEstimate: {
            deps: ['jquery']
        }
    }
};