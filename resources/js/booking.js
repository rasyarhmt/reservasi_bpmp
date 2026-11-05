const rupiah = (amount) =>
    'Rp' + new Intl.NumberFormat('id-ID').format(Math.round(amount));

export function roomBooking(payload) {
    return {
        form: payload.form,
        rooms: payload.rooms,
        minDownPayment: payload.minDownPayment ?? 100000,
        minDate: payload.minDate,
        availabilityUrl: payload.availabilityUrl,
        unavailableRoomIds: [],
        loadingAvailability: false,
        availabilityRequestId: 0,

        init() {
            this.$watch('form.check_in_date', () => this.refreshAvailability());
            this.$watch('form.check_out_date', () => this.refreshAvailability());
            this.refreshAvailability();
        },

        async refreshAvailability() {
            const { check_in_date, check_out_date } = this.form;
            const requestId = ++this.availabilityRequestId;

            if (
                !this.availabilityUrl ||
                !check_in_date ||
                !check_out_date ||
                check_out_date <= check_in_date
            ) {
                this.unavailableRoomIds = [];
                this.loadingAvailability = false;
                return;
            }

            this.loadingAvailability = true;

            try {
                const url = new URL(this.availabilityUrl, window.location.origin);
                url.searchParams.set('check_in_date', check_in_date);
                url.searchParams.set('check_out_date', check_out_date);

                const response = await fetch(url.toString(), {
                    headers: { Accept: 'application/json' },
                });

                if (requestId !== this.availabilityRequestId) {
                    return;
                }

                if (response.ok) {
                    const data = await response.json();
                    this.unavailableRoomIds = Array.isArray(data.unavailable_room_ids)
                        ? data.unavailable_room_ids.map(Number)
                        : [];

                    if (this.isRoomUnavailable(this.form.room_id)) {
                        this.form.room_id = '';
                    }
                }
            } catch (error) {
                if (requestId === this.availabilityRequestId) {
                    this.unavailableRoomIds = [];
                }
            } finally {
                if (requestId === this.availabilityRequestId) {
                    this.loadingAvailability = false;
                }
            }
        },

        isRoomUnavailable(roomId) {
            if (!roomId) return false;
            return this.unavailableRoomIds.includes(Number(roomId));
        },

        get hasUnavailableRooms() {
            return this.unavailableRoomIds.length > 0;
        },

        get room() {
            return this.rooms.find((room) => room.id == this.form.room_id) ?? null;
        },

        get nights() {
            if (!this.form.check_in_date || !this.form.check_out_date) return 0;
            const diff = new Date(this.form.check_out_date) - new Date(this.form.check_in_date);
            return diff > 0 ? Math.round(diff / 86400000) : 0;
        },

        get extraGuests() {
            if (!this.room) return 0;
            return Math.max(0, (Number(this.form.guest_count) || 0) - this.room.base_capacity);
        },

        get total() {
            if (!this.room || !this.nights) return 0;
            const perNight = Number(this.room.base_price) + this.extraGuests * Number(this.room.extra_person_rate);
            return perNight * this.nights;
        },

        get downPaymentAmount() {
            return Number(this.form.down_payment_amount) || 0;
        },

        get dueAmount() {
            if (this.form.payment_scheme !== 'dp') return this.total;
            return this.downPaymentAmount > 0 ? this.downPaymentAmount : 0;
        },

        get downPaymentInvalid() {
            if (this.form.payment_scheme !== 'dp') return false;
            const amount = this.downPaymentAmount;
            if (amount === 0) return true;
            return amount < this.minDownPayment || amount > this.total;
        },

        rupiah,
    };
}

export function hallBooking(payload) {
    return {
        form: payload.form,
        halls: payload.halls,
        minDownPayment: payload.minDownPayment ?? 100000,
        availabilityUrl: payload.availabilityUrl,
        bookedDates: [],
        loadingDates: false,

        init() {
            this.fetchBookedDates();
        },

        async fetchBookedDates() {
            if (!this.availabilityUrl || !this.form.hall_id) {
                return;
            }

            this.loadingDates = true;

            try {
                const url = new URL(this.availabilityUrl, window.location.origin);
                url.searchParams.set('hall_id', this.form.hall_id);
                const response = await fetch(url.toString(), {
                    headers: { Accept: 'application/json' },
                });

                if (response.ok) {
                    const data = await response.json();
                    this.bookedDates = Array.isArray(data.dates) ? data.dates : [];
                }
            } catch (error) {
                this.bookedDates = [];
            } finally {
                this.loadingDates = false;
            }
        },

        get hall() {
            return this.halls.find((hall) => hall.id == this.form.hall_id) ?? null;
        },

        get isDateBooked() {
            if (!this.form.hall_date) return false;
            return this.bookedDates.includes(this.form.hall_date);
        },

        get dateLabel() {
            if (!this.form.hall_date) return '—';
            return new Date(this.form.hall_date + 'T00:00:00').toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            });
        },

        get total() {
            if (!this.hall || !this.form.hall_date || this.isDateBooked) return 0;
            return Number(this.hall.daily_rate);
        },

        get downPaymentAmount() {
            return Number(this.form.down_payment_amount) || 0;
        },

        get dueAmount() {
            if (this.isDateBooked) return 0;
            if (this.form.payment_scheme !== 'dp') return this.total;
            return this.downPaymentAmount > 0 ? this.downPaymentAmount : 0;
        },

        get downPaymentInvalid() {
            if (this.form.payment_scheme !== 'dp') return false;
            const amount = this.downPaymentAmount;
            if (amount === 0) return true;
            return amount < this.minDownPayment || amount > this.total;
        },

        rupiah,
    };
}
