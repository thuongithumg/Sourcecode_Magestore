/*
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [
        'jquery',
        'ko',
        'Magestore_Webpos/js/model/event-manager',
        'Magestore_Webpos/js/model/shift/shift',
        'Magestore_Webpos/js/helper/price',
        'Magestore_Webpos/js/helper/shift',
        'Magestore_Webpos/js/helper/datetime',
    ],
    function ($, ko, Event, shiftModel, priceHelper, shiftHelper, datetimeHelper) {
        "use strict";

        return {
            execute: function() {
                var self = this;
                Event.observer('webpos_place_order_after',function(event,data){
                    self.updateShiftData(data);
                });
                Event.observer('webpos_place_order_online_after',function(event,data){
                    self.updateShiftData(data);
                });
            },
            updateShiftData: function(data){
                if(data){
                    if(!window.webposConfig.shiftId){
                        return;
                    }
                    var methodData = [];
                    if(data.webpos_order_payments != undefined){
                        methodData = data.webpos_order_payments;
                    }
                    var cashValue = 0;
                    var discountAmount = data.discount_amount;
                    var grandTotal = 0;

                    var orderId = data.increment_id;

                    ko.utils.arrayForEach(methodData, function(item) {
                        grandTotal = grandTotal + priceHelper.toPositiveNumber(item.display_amount);
                        if (item.method == 'cashforpos'){
                            cashValue = priceHelper.toPositiveNumber(item.display_amount);
                        }
                    });

                    var currentShift = shiftModel();
                    var deferred = currentShift.load(window.webposConfig.shiftId);
                    deferred.done(function (currentShiftData) {
                        var balance = priceHelper.toPositiveNumber(currentShiftData.balance) + priceHelper.toPositiveNumber(cashValue);
                        var transactionData = {
                            'shift_id':window.webposConfig.shiftId,
                            'location_id': window.webposConfig.locationId,
                            'value': cashValue,
                            'base_value': priceHelper.currencyConvert(cashValue, window.webposConfig.currentCurrencyCode,window.webposConfig.baseCurrencyCode),
                            'note': 'Add cash from order with id = ' + orderId,
                            'staff_name': window.webposConfig.staffName,
                            'staff_id': window.webposConfig.staffId,
                            'balance': balance,
                            'base_balance': priceHelper.currencyConvert(balance, window.webposConfig.currentCurrencyCode,window.webposConfig.baseCurrencyCode),
                            'type': 'order',
                            'base_currency_code': window.webposConfig.baseCurrencyCode,
                            'transaction_currency_code': window.webposConfig.currentCurrencyCode,
                            'created_at': data.created_at
                        };

                        //update data for shift.
                        currentShiftData.balance = balance;
                        currentShiftData.base_balance = transactionData.base_balance;
                        currentShiftData.cash_sale = priceHelper.toPositiveNumber(currentShiftData.cash_sale) + priceHelper.toPositiveNumber(cashValue);
                        currentShiftData.base_cash_sale = priceHelper.currencyConvert(currentShiftData.cash_sale, window.webposConfig.currentCurrencyCode,window.webposConfig.baseCurrencyCode);
                        currentShiftData.total_sales = priceHelper.toPositiveNumber(currentShiftData.total_sales) + priceHelper.toPositiveNumber(grandTotal);
                        currentShiftData.base_total_sales = priceHelper.currencyConvert(currentShiftData.total_sales, window.webposConfig.currentCurrencyCode,window.webposConfig.baseCurrencyCode);

                        //update cash transaction
                        if(cashValue > 0){
                            var cashTransaction = [];
                            if(currentShiftData.cash_transaction){
                                cashTransaction = currentShiftData.cash_transaction;
                            }
                            cashTransaction.push(transactionData);
                            currentShiftData.cash_transaction = cashTransaction;
                        }


                        //update sale summary
                        var newSaleSummaryData = {};
                        var methodCode = [];
                        var newItem = {};

                        ko.utils.arrayForEach(methodData, function(methodItem) {
                            newItem = {};
                            newItem['payment_method'] = methodItem.method;
                            newItem['payment_amount'] = methodItem.display_amount;
                            newItem['base_payment_amount'] = methodItem.base_display_amount;
                            newItem['method_title'] = methodItem.method_title;
                            newSaleSummaryData[methodItem.method] = newItem;
                            methodCode.push(methodItem.method);
                        });

                        if(currentShiftData.sale_summary) {
                            ko.utils.arrayForEach(currentShiftData.sale_summary, function (saleSummaryItem) {
                                var method = saleSummaryItem['payment_method'];

                                if(newSaleSummaryData[method]){
                                    var item1 = newSaleSummaryData[method];
                                    var item2 =  saleSummaryItem;
                                    newItem = {};
                                    newItem['payment_method'] = method;
                                    newItem['payment_amount'] = priceHelper.toPositiveNumber(item1.payment_amount) + priceHelper.toPositiveNumber(item2.payment_amount);
                                    newItem['base_payment_amount'] = priceHelper.toPositiveNumber(item1.base_payment_amount) + priceHelper.toPositiveNumber(item2.base_payment_amount);
                                    newItem['method_title'] = item1.method_title;
                                    newSaleSummaryData[method] = newItem;
                                }
                                else {
                                    newSaleSummaryData[method] = saleSummaryItem;
                                    methodCode.push(method);
                                }

                            });
                        }
                        var sale_summary = [];
                        methodCode.forEach(
                            function (method, index) {
                                if(newSaleSummaryData[method]['payment_amount'] > 0){
                                    sale_summary.push(newSaleSummaryData[method]);
                                }

                            }
                        );
                        currentShiftData.sale_summary = sale_summary;

                        //update zreport data
                        var zReportData = currentShiftData.zreport_sales_summary;
                        if(zReportData) {
                            zReportData.grand_total = currentShiftData.total_sales;
                            zReportData.discount_amount= priceHelper.toPositiveNumber(zReportData.discount_amount) + priceHelper.toPositiveNumber(discountAmount);
                            currentShiftData.zreport_sales_summary = zReportData;
                        }
                        var self = this;
                        var model = shiftModel();
                        var deferred = model.setData(currentShiftData).setMode('offline').save();

                        deferred.done(function (response) {
                            if(response){
                                Event.dispatch('refresh_shift_listing',response);
                            }
                        });
                    });


                }
            }
        }
    }
);