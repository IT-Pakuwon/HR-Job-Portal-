// ============================================================
// route.js — Voucher Taxi
// Route validation (single origin → destination, no table management)
// ============================================================

const VoucherTaxiRoute = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    // VoucherTaxi only has single origin/destination, not a route table
    state: {
        currentOrigin:     '',
        currentDestination: '',
    },

    // --------------------------------------------------------
    // INIT — called once on page load (minimal for VoucherTaxi)
    // --------------------------------------------------------
    init() {
        // VoucherTaxi doesn't have route table management
        // This module is kept for consistency with BookingCar architecture
        // But routes are simple form fields, not table rows
    },

    // --------------------------------------------------------
    // CLEAR (called when closing forms)
    // --------------------------------------------------------
    clearCreate() {
        // Reset origin/destination when closing create form
        VoucherTaxiHelper.setValue('origin', '');
        VoucherTaxiHelper.setValue('destination', '');
        VoucherTaxiRoute.state.currentOrigin = '';
        VoucherTaxiRoute.state.currentDestination = '';
    },

    clearEdit() {
        // Reset origin/destination when closing edit form
        VoucherTaxiHelper.setValue('edit_origin', '');
        VoucherTaxiHelper.setValue('edit_destination', '');
        VoucherTaxiRoute.state.currentOrigin = '';
        VoucherTaxiRoute.state.currentDestination = '';
    },

    // --------------------------------------------------------
    // GET ROUTE (single route only)
    // --------------------------------------------------------
    getCreateRoute() {
        const origin = VoucherTaxiHelper.getValue('origin')?.trim() ?? '';
        const destination = VoucherTaxiHelper.getValue('destination')?.trim() ?? '';

        if (!origin && !destination) {
            return null;
        }

        return {
            origin,
            destination,
        };
    },

    getEditRoute() {
        const origin = VoucherTaxiHelper.getValue('edit_origin')?.trim() ?? '';
        const destination = VoucherTaxiHelper.getValue('edit_destination')?.trim() ?? '';

        if (!origin && !destination) {
            return null;
        }

        return {
            origin,
            destination,
        };
    },

    // --------------------------------------------------------
    // VALIDATE — simple origin ≠ destination check
    // --------------------------------------------------------
    validateRoute(origin, destination) {
        // Trim whitespace
        const o = (origin ?? '').toString().trim();
        const d = (destination ?? '').toString().trim();

        // Both required
        if (VoucherTaxiHelper.isEmpty(o)) {
            VoucherTaxi.toast('warning', 'Origin is required.');
            return false;
        }

        if (VoucherTaxiHelper.isEmpty(d)) {
            VoucherTaxi.toast('warning', 'Destination is required.');
            return false;
        }

        // Must differ
        if (o.toLowerCase() === d.toLowerCase()) {
            VoucherTaxi.toast('warning', 'Origin and destination cannot be the same.');
            return false;
        }

        return true;
    },

    // --------------------------------------------------------
    // VALIDATE ROUTES (wrapper for consistency)
    // --------------------------------------------------------
    validateRoutes(routes) {
        // routes can be an array or a single route object
        // For VoucherTaxi, we typically have just one route

        if (!routes) {
            VoucherTaxi.toast('warning', 'Route is required.');
            return false;
        }

        // If array, check each
        if (Array.isArray(routes)) {
            if (routes.length === 0) {
                VoucherTaxi.toast('warning', 'At least one route is required.');
                return false;
            }

            for (let i = 0; i < routes.length; i++) {
                const route = routes[i];
                if (VoucherTaxiHelper.isEmpty(route.origin)) {
                    VoucherTaxi.toast('warning', `Row ${i + 1}: Origin is required.`);
                    return false;
                }

                if (VoucherTaxiHelper.isEmpty(route.destination)) {
                    VoucherTaxi.toast('warning', `Row ${i + 1}: Destination is required.`);
                    return false;
                }

                if (route.origin.toLowerCase() === route.destination.toLowerCase()) {
                    VoucherTaxi.toast('warning', `Row ${i + 1}: Origin and destination cannot be the same.`);
                    return false;
                }
            }
        } else {
            // Single route object
            return VoucherTaxiRoute.validateRoute(routes.origin, routes.destination);
        }

        return true;
    },

    // --------------------------------------------------------
    // POPULATE EDIT ROUTE
    // --------------------------------------------------------
    loadEditRoute(origin = '', destination = '') {
        VoucherTaxiHelper.setValue('edit_origin', origin);
        VoucherTaxiHelper.setValue('edit_destination', destination);
        VoucherTaxiRoute.state.currentOrigin = origin;
        VoucherTaxiRoute.state.currentDestination = destination;
    },

    // --------------------------------------------------------
    // GET ROUTE SUMMARY (for display)
    // --------------------------------------------------------
    getRouteSummary(origin, destination) {
        if (VoucherTaxiHelper.isEmpty(origin) || VoucherTaxiHelper.isEmpty(destination)) {
            return 'Not specified';
        }

        return `${origin} → ${destination}`;
    },

    // --------------------------------------------------------
    // AUTO-FILL DESTINATION (if needed)
    // --------------------------------------------------------
    autoFillDestination(origin) {
        // Could implement common routes or reverse trip logic
        // For now, just a placeholder
        return '';
    },

    // --------------------------------------------------------
    // VALIDATE RETURN TRIP
    // --------------------------------------------------------
    validateReturnTrip(outbound, inbound) {
        // For Return trips, validate that inbound is reverse of outbound
        if (!outbound || !inbound) return true;

        if (outbound.origin === inbound.destination &&
            outbound.destination === inbound.origin) {
            return true;
        }

        return false;
    },

    // --------------------------------------------------------
    // FORMAT ROUTE
    // --------------------------------------------------------
    formatRoute(route) {
        if (!route) return '-';

        const origin = route.origin ?? '';
        const destination = route.destination ?? '';

        if (!origin && !destination) return '-';

        return `${origin} → ${destination}`;
    },
};
