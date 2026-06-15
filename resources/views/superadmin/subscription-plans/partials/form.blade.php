@php
    $plan = $plan ?? null;
@endphp

<label class="space-y-2 {{ $plan ? 'md:col-span-2' : '' }}">
    <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Plan Name</span>
    <input name="name" value="{{ old('name', $plan?->name) }}" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white" required>
</label>

<label class="space-y-2 {{ $plan ? 'md:col-span-2' : '' }}">
    <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Description</span>
    <textarea name="description" rows="3" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white">{{ old('description', $plan?->description) }}</textarea>
</label>

<label class="space-y-2">
    <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Price</span>
    <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $plan?->price ?? 0) }}" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white" required>
</label>

<label class="space-y-2">
    <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Currency</span>
    <input name="currency" maxlength="3" value="{{ old('currency', $plan?->currency ?? 'MYR') }}" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold uppercase text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white" required>
</label>

<label class="space-y-2">
    <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Billing</span>
    <select name="billing_interval" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white" required>
        @foreach(['monthly' => 'Monthly', 'annual' => 'Annual', 'lifetime' => 'Lifetime'] as $value => $label)
            <option value="{{ $value }}" @selected(old('billing_interval', $plan?->billing_interval ?? 'monthly') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</label>

<label class="space-y-2">
    <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Sort Order</span>
    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white">
</label>

<label class="space-y-2">
    <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Max Students</span>
    <input type="number" min="0" name="max_students" value="{{ old('max_students', $plan?->max_students) }}" placeholder="Unlimited" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white">
</label>

<label class="space-y-2">
    <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Max Teachers</span>
    <input type="number" min="0" name="max_teachers" value="{{ old('max_teachers', $plan?->max_teachers) }}" placeholder="Unlimited" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white">
</label>

<label class="space-y-2 {{ $plan ? '' : 'md:col-span-2' }}">
    <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Max Admins</span>
    <input type="number" min="0" name="max_admins" value="{{ old('max_admins', $plan?->max_admins) }}" placeholder="Unlimited" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white">
</label>

<label class="flex items-center gap-3 rounded-2xl bg-[#f7f2ea] px-4 py-3 dark:bg-gray-950 {{ $plan ? 'md:col-span-2' : '' }}">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan?->is_active ?? true)) class="rounded border-[#eadfce] text-[#d97706] focus:ring-[#d97706]">
    <span class="text-sm font-extrabold text-[#6b5f52] dark:text-gray-300">Active plan</span>
</label>

<div class="{{ $plan ? 'md:col-span-2' : '' }} flex justify-end">
    <button class="rounded-2xl bg-[#d97706] px-5 py-3 text-sm font-extrabold text-white shadow-sm">{{ $button }}</button>
</div>
