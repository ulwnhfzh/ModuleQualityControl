<div>
    <label class="block text-sm font-semibold mb-1">Perusahaan</label>
    <select name="company" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="HAMATETSU" @selected(($selectedCompany ?? 'HAMATETSU') === 'HAMATETSU')>HAMATETSU</option>
        <option value="NISSO GIKEN" @selected(($selectedCompany ?? 'HAMATETSU') === 'NISSO GIKEN')>NISSO GIKEN</option>
    </select>
</div>
