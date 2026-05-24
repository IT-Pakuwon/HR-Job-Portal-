(function () {
    'use strict';

    VoucherTaxi.Route = {

        base: '/vouchertaxi',

        json() {
            return `${this.base}/json`;
        },

        detail(eid) {
            return `${this.base}/detail/${eid}`;
        },

        tracking(eid) {
            return `${this.base}/tracking/${eid}`;
        },

        find(eid) {
            return `${this.base}/find/${eid}`;
        },

        purposeSearch() {
            return `${this.base}/purpose-search`;
        },

        employeeByDepartment() {
            return `${this.base}/employee-by-department`;
        },

        store() {
            return `${this.base}/store`;
        },

        update(docid) {
            return `${this.base}/update/${docid}`;
        },

        cancel(docid) {
            return `${this.base}/cancel/${docid}`;
        },

        approve(docid) {
            return `${this.base}/approve/${docid}`;
        },

        reject(docid) {
            return `${this.base}/reject/${docid}`;
        },

        revise(docid) {
            return `${this.base}/revise/${docid}`;
        },

        process(docid) {
            return `${this.base}/process/${docid}`;
        },

        print(hash) {
            return `${this.base}/print/${hash}`;
        },

        show(eid) {
            return `/showvouchertaxi/${eid}`;
        }
    };

    VoucherTaxi.log('Route Loaded');

})();
