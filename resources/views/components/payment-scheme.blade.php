<div>
    <div class="grid gap-4 sm:grid-cols-2">
        <label
            class="flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-colors"
            :class="form.payment_scheme === 'dp' ? 'border-foreground ring-1 ring-foreground' : ''">
            <input type="radio" name="payment_scheme" value="dp"
                x-model="form.payment_scheme" class="mt-1">
            <span>
                <span class="block font-medium">DP (nominal bebas)</span>
                <span class="text-sm text-muted-foreground">
                    Bayar sebagian sekarang, sisanya saat check-out.
                </span>
            </span>
        </label>

        <label
            class="flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-colors"
            :class="form.payment_scheme === 'lunas' ? 'border-foreground ring-1 ring-foreground' : ''">
            <input type="radio" name="payment_scheme" value="lunas"
                x-model="form.payment_scheme" class="mt-1">
            <span>
                <span class="block font-medium">Lunas</span>
                <span class="text-sm text-muted-foreground">
                    Bayar seluruh tagihan di muka.
                </span>
            </span>
        </label>
    </div>

    <div x-show="form.payment_scheme === 'dp'" x-cloak class="mt-4 space-y-2">
        <x-label for="down_payment_amount">Nominal DP</x-label>
        <x-input id="down_payment_amount" name="down_payment_amount" type="number"
            x-model="form.down_payment_amount"
            x-bind:min="minDownPayment"
            step="1000" placeholder="Contoh: 110000" />
        <p class="text-xs text-muted-foreground">
            Minimal <span x-text="rupiah(minDownPayment)"></span>.
        </p>
        <p x-show="form.payment_scheme === 'dp' && downPaymentAmount > total && total > 0" x-cloak class="text-xs text-destructive">
            Nominal DP tidak boleh melebihi total tagihan.
        </p>
        <p x-show="form.payment_scheme === 'dp' && downPaymentAmount > 0 && downPaymentAmount < minDownPayment" x-cloak class="text-xs text-destructive">
            Nominal DP minimal <span x-text="rupiah(minDownPayment)"></span>.
        </p>
        <x-input-error name="down_payment_amount" class="mt-1" />
    </div>

    <x-input-error name="payment_scheme" class="mt-2" />
</div>
