<div class="agent-info rounded-2xl border border-slate-200/80 bg-white p-6 shadow-lg shadow-slate-200/50 transition-all hover:shadow-xl">

    <table class="w-full text-sm">

        <tr class="border-b border-slate-100 last:border-0">
            <td width="30%" class="py-3 pr-4 font-semibold text-slate-600">
                Nom et Prénoms :
            </td>
            <td class="py-3 font-medium text-slate-800">
                <strong>{{ $agent->full_name }}</strong>
            </td>
        </tr>

        <tr class="border-b border-slate-100 last:border-0">
            <td class="py-3 pr-4 font-semibold text-slate-600">
                IM :
            </td>
            <td class="py-3 font-medium text-slate-800">
                {{ $agent->matricule ?: '—' }}
            </td>
        </tr>

        <tr class="border-b border-slate-100 last:border-0">
            <td class="py-3 pr-4 font-semibold text-slate-600">
                Corps et Grade :
            </td>
            <td class="py-3 font-medium text-slate-800">
                {{ $agent->corps ?: '—' }}
                <span class="mx-1 text-slate-300">/</span>
                {{ $agent->grade ?: '—' }}
            </td>
        </tr>

        <tr class="border-b border-slate-100 last:border-0">
            <td class="py-3 pr-4 font-semibold text-slate-600">
                Budget :
            </td>
            <td class="py-3 font-medium text-slate-800">
                {{ $agent->budget ?: '—' }}
                <span class="mx-2 text-slate-300">|</span>
                <span class="font-semibold text-slate-600">Chapitre :</span>
                {{ $agent->chapitre ?: '—' }}
            </td>
        </tr>

        <tr class="border-b border-slate-100 last:border-0">
            <td class="py-3 pr-4 font-semibold text-slate-600">
                Direction :
            </td>
            <td class="py-3 font-medium text-slate-800">
                {{ $agent->direction ?: '—' }}
            </td>
        </tr>

        <tr class="border-b border-slate-100 last:border-0">
            <td class="py-3 pr-4 font-semibold text-slate-600">
                Service :
            </td>
            <td class="py-3 font-medium text-slate-800">
                {{ $agent->service ?: '—' }}
            </td>
        </tr>

        <tr class="border-b border-slate-100 last:border-0">
            <td class="py-3 pr-4 font-semibold text-slate-600">
                Fonction :
            </td>
            <td class="py-3 font-medium text-slate-800">
                {{ $agent->fonction ?: '—' }}
            </td>
        </tr>

    </table>

</div>