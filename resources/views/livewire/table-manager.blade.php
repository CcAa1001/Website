<div class="col-md-12 mb-3">
    <label class="form-label">QR Code (Optional)</label>
    <div class="input-group input-group-outline @if($qr_code) is-filled @endif">
        <input wire:model.defer="qr_code" type="text" class="form-control" placeholder="Leave empty to auto-generate">
    </div>
    @error('qr_code') <span class="text-danger text-xs">{{ $message }}</span> @enderror
    <small class="text-muted">You can manually type a code from a sticker or leave blank.</small>
</div>