/*
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [
        'jquery',
        'ko',
        'Magestore_Webpos/js/model/collection/abstract',
        'Magestore_Webpos/js/model/resource-model/magento-rest/customer/credit-history',
        'Magestore_Webpos/js/model/resource-model/indexed-db/customer/credit-history'

    ],
    function ($,ko, collectionAbstract, creditRest, creditIndexedDb) {
        "use strict";

        return collectionAbstract.extend({
            /* Query Params*/
            queryParams: {
                filterParams: [],
                orderParams: [],
                pageSize: '',
                currentPage: '',
                paramToFilter: [],
                paramOrFilter: []
            },
            initialize: function () {
                this._super();
                this.setResource(creditRest(), creditIndexedDb());
            }
        });
    }
);