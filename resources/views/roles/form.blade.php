<!-- Campo Nombre -->
<div class="mb-6">
    <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Rol</label>
    <input type="text" id="name" name="name" value="{{ old('name', $role->name ?? '') }}" autocomplete="off" required
        class="mt-1 block w-full px-3 py-2 border 
        @error('name') border-red-500 @else border-gray-300 @enderror
        rounded-md shadow-sm focus:outline-none 
        @error('name') focus:ring-red-500 focus:border-red-500 @else focus:ring-blue-500 focus:border-blue-500 @enderror 
        sm:text-sm">
    @error('name')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<!-- Lista de Permisos con Checkboxes -->
<div class="mb-6">
    <label for="permissions" class="block text-sm font-medium text-gray-700">Permisos</label>
    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($permissions as $permission)
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out"
                    {{ in_array($permission->id, old('permissions', $rolePermissions ?? [])) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">{{ $permission->name }}</span>
                </label>
            </div>
        @endforeach
    </div>
    @error('permissions')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
