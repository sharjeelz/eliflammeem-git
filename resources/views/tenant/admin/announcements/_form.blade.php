{{-- Title --}}
<div class="mb-5">
    <label class="form-label fw-semibold required">Title</label>
    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
        value="{{ old('title', $announcement->title ?? '') }}"
        placeholder="e.g. Transport route improved for North Campus" maxlength="200" required>
    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

{{-- Body --}}
<div class="mb-5">
    <label class="form-label fw-semibold required">Message</label>
    <textarea name="body" rows="6" class="form-control @error('body') is-invalid @enderror"
        placeholder="Describe what was done in response to parent/teacher feedback…" maxlength="5000" required>{{ old('body', $announcement->body ?? '') }}</textarea>
    @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="form-text">Parents see this on the portal. Be specific — mention what changed.</div>
</div>

{{-- Category --}}
<div class="mb-5">
    <label class="form-label fw-semibold">Related Category <span class="text-muted">(optional)</span></label>
    <select name="category_id" class="form-select">
        <option value="">— No specific category —</option>
        @foreach($categories as $cat)
        <option value="{{ $cat->id }}"
            @selected(old('category_id', $announcement->issue_category_id ?? '') == $cat->id)>
            {{ $cat->name }}
        </option>
        @endforeach
    </select>
</div>

{{-- Published at --}}
<div class="mb-5">
    <label class="form-label fw-semibold">Publish Date <span class="text-muted">(leave blank to save as draft)</span></label>
    <input type="datetime-local" name="published_at" class="form-control @error('published_at') is-invalid @enderror"
        value="{{ old('published_at', isset($announcement) && $announcement->published_at ? $announcement->published_at->format('Y-m-d\TH:i') : '') }}">
    @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="form-text">Set to a future date to schedule, or a past/current date to publish immediately.</div>
</div>
