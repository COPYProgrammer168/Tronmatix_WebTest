@php
    $levelNames = ['category', 'main-category', 'sub-category', 'brand'];
    $addLabels = ['ADD MAIN CATE', 'ADD SUB CATE', 'ADD BRAND', null];
    $typeLabel = $levelNames[$level] ?? 'category';
    $addLabel = $addLabels[$level] ?? null;
    $canHaveChildren = $level < 3;
@endphp

@foreach($items as $item)
    @php
        $id = $item->id;
        $name = $item->name;
        $order = $item->order;
        $isActive = $item->is_active;
        $childKey = match($level) {
            0 => 'mainCategories',
            1 => 'subCategories',
            2 => 'brands',
            default => null,
        };
        $children = $childKey ? ($item->{$childKey} ?? []) : [];
        // Only show an expand/collapse chevron when this node actually HAS
        // children. A main category with no sub-categories gets no arrow.
        $hasKids = $canHaveChildren && count($children) > 0;
        $parentId = match($level) {
            0 => '',
            1 => $item->category_id ?? '',
            2 => $item->main_category_id ?? '',
            3 => $item->sub_category_id ?? '',
            default => '',
        };
        $parentName = match($level) {
            0 => '',
            1 => ($item->category->name ?? ''),
            2 => ($item->mainCategory->category->name ?? '') . ' → ' . ($item->mainCategory->name ?? ''),
            3 => ($item->subCategory->mainCategory->category->name ?? '') . ' → ' . ($item->subCategory->mainCategory->name ?? '') . ' → ' . ($item->subCategory->name ?? ''),
            default => '',
        };
    @endphp

    <div class="cm-tree-node" data-id="{{ $id }}" data-type="{{ $typeLabel }}" data-parent-id="{{ $parentId }}">
        {{-- Tree row --}}
        <div class="cm-tree-row" data-id="{{ $id }}" data-type="{{ $typeLabel }}">
            {{-- Drag handle --}}
            <div class="cm-drag-handle" title="{{ __('dashboard.categories.dragReorder') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="9" cy="6" r="2"/><circle cx="15" cy="6" r="2"/>
                    <circle cx="9" cy="12" r="2"/><circle cx="15" cy="12" r="2"/>
                    <circle cx="9" cy="18" r="2"/><circle cx="15" cy="18" r="2"/>
                </svg>
            </div>

            {{-- Expand/collapse chevron — only when the node has children --}}
            @if($hasKids)
                <div class="cm-chevron"
                     onclick="toggleNode(this)"
                     title="{{ __('dashboard.categories.expandCollapse') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <path d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            @else
                <div style="width:20px; flex-shrink:0;"></div>
            @endif

            {{-- Name --}}
            <div class="cm-node-name">{{ $name }}</div>

            {{-- Order --}}
            <div class="cm-node-order" title="{{ __('dashboard.categories.order') }}">#{{ $order }}</div>

            {{-- Active toggle --}}
            <label class="cm-toggle" title="{{ $isActive ? __('dashboard.categories.disable') : __('dashboard.categories.enable') }}">
                <input type="checkbox" {{ $isActive ? 'checked' : '' }}
                       onchange="toggleActive('{{ $typeLabel }}', {{ $id }}, this)">
                <span class="cm-toggle-slider"></span>
            </label>

            {{-- Edit --}}
            @php $editData = ["id"=>$id,"name"=>$name,"order"=>$order,"is_active"=>$isActive,"parentId"=>$parentId,"parentName"=>$parentName]; @endphp
            <button class="cm-btn-icon" title="{{ __('dashboard.categories.edit') }}"
                    onclick='openModal("{{ $typeLabel }}", @json($editData))'>
                ✏️
            </button>

            {{-- Delete --}}
            <button class="cm-btn-icon cm-btn-delete" title="{{ __('dashboard.categories.deleteTooltip') }}"
                    onclick="promptDelete('{{ $typeLabel }}', {{ $id }}, '{{ addslashes($name) }}')">
                🗑️
            </button>

            {{-- Add child --}}
            @if($addLabel && $level < 3)
                @php $addData = ["parentId"=>$id,"parentName"=>$name]; @endphp
                <button class="cm-btn-icon cm-btn-add" title="{{ __('dashboard.categories.addChild') }}"
                        onclick='openModal("{{ $levelNames[$level + 1] }}", @json($addData))'>
                    +
                </button>
            @endif
        </div>

        {{-- Children container — only when the node actually has children --}}
        @if($hasKids)
            <div class="cm-children collapsed">
                @include('dashboard.category-management.partials.tree', [
                    'items' => $children,
                    'level' => $level + 1,
                ])
            </div>
        @endif
    </div>
@endforeach
