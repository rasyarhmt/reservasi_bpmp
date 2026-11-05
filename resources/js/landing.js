const rupiah = (amount) =>
    'Rp' + new Intl.NumberFormat('id-ID').format(Math.round(amount));

/**
 * Kalkulator pratinjau biaya untuk halaman depan.
 *
 * Menggunakan formula yang sama dengan app/Services/BookingService.php:
 *   kamar -> (harga dasar + tarif tambahan) x jumlah malam
 *   aula  -> tarif per hari (satu tanggal, 08:00-16:00)
 */
export function landingCalculator(payload) {
    return {
        mode: 'room',
        rooms: payload.rooms ?? [],
        halls: payload.halls ?? [],
        roomId: payload.rooms?.[0]?.id ?? null,
        hallId: payload.halls?.[0]?.id ?? null,
        nights: 1,
        guests: 0,

        init() {
            this.guests = this.baseCapacity || 1;
        },

        get room() {
            return this.rooms.find((room) => room.id == this.roomId) ?? null;
        },

        get hall() {
            return this.halls.find((hall) => hall.id == this.hallId) ?? null;
        },

        get baseCapacity() {
            return Number(this.room?.base_capacity ?? 0);
        },

        get basePrice() {
            return Number(this.room?.base_price ?? 0);
        },

        get extraPersonRate() {
            return Number(this.room?.extra_person_rate ?? 0);
        },

        get maxGuests() {
            return this.baseCapacity + 1;
        },

        get extraGuests() {
            return Math.max(0, (Number(this.guests) || 0) - this.baseCapacity);
        },

        get roomBaseTotal() {
            return this.basePrice * this.nights;
        },

        get roomExtraTotal() {
            return this.extraGuests * this.extraPersonRate * this.nights;
        },

        get roomTotal() {
            return this.roomBaseTotal + this.roomExtraTotal;
        },

        get hallTotal() {
            return this.dailyRate;
        },

        get dailyRate() {
            return Number(this.hall?.daily_rate ?? 0);
        },

        get total() {
            return this.mode === 'room' ? this.roomTotal : this.dailyRate;
        },

        get isValid() {
            return this.mode === 'room'
                ? Boolean(this.room) && this.nights > 0
                : Boolean(this.hall);
        },

        syncGuests() {
            this.guests = this.baseCapacity;
        },

        stepNights(delta) {
            this.nights = Math.min(30, Math.max(1, this.nights + delta));
        },

        stepGuests(delta) {
            this.guests = Math.min(this.maxGuests, Math.max(1, this.guests + delta));
        },

        rupiah,
    };
}
