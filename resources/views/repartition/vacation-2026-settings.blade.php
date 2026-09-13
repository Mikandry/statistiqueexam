<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Paramètres des documents</h2>
        <p class="mt-1 text-sm text-slate-600">Ces informations sont reprises dans les notes, décisions et états de vacation.</p>
        <form method="POST" action="{{ route('vacation2026.settings.update') }}" class="mt-4 space-y-3">
            @csrf
            <textarea name="entete" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Entête">{{ old('entete', $setting?->entete) }}</textarea>
            <textarea name="considerant" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Considérant">{{ old('considerant', $setting?->considerant) }}</textarea>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <input name="note_titre" value="{{ old('note_titre', $setting?->note_titre) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Titre note de service">
                <input name="decision_titre" value="{{ old('decision_titre', $setting?->decision_titre) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Titre décision">
                <input name="presence_titre" value="{{ old('presence_titre', $setting?->presence_titre) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Titre fiche de présence">
                <input name="decompte_titre" value="{{ old('decompte_titre', $setting?->decompte_titre) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Titre état de décompte">
            </div>
            <textarea name="decision_article_1" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Article 1 de la décision">{{ old('decision_article_1', $setting?->decision_article_1) }}</textarea>
            <textarea name="decision_article_2" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Article 2 de la décision">{{ old('decision_article_2', $setting?->decision_article_2) }}</textarea>
            <input name="signature" value="{{ old('signature', $setting?->signature) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Signataire">
            <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600">Enregistrer les paramètres</button>
        </form>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Ajouter une activité</h2>
        <form method="POST" action="{{ route('vacation2026.activities.store') }}" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
            @csrf
            <input name="examen" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Examen (CEPE, BEPC…)">
            <input name="libelle" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Libellé de l'activité">
            <select name="level" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach($availableLevels as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select name="phase" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="AVANT_SESSION">Avant session</option>
                <option value="PENDANT_SESSION">Pendant session</option>
                <option value="APRES_SESSION">Après session</option>
                <option value="AVANT_EPREUVES_EPS">Avant épreuves EPS</option>
                <option value="PENDANT_EPREUVES_EPS">Pendant épreuves EPS</option>
                <option value="APRES_EPREUVES_EPS">Après épreuves EPS</option>
            </select>
            <input type="number" min="1" name="max_agents" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Nombre maximal d'agents">
            <input type="number" min="1" name="nb_jours" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Nombre de jours">
            <input type="number" step="0.01" min="0" name="taux_activite" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Taux">
            <input type="number" min="0" name="ordre" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Ordre">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 md:col-span-2">Ajouter l'activité</button>
        </form>
    </section>
</div>

<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-900">Activités et phases de session</h2>
    <p class="mt-1 text-sm text-slate-600">Modifiez ici la phase avant, pendant ou après session. Une activité affectée ne peut pas être supprimée.</p>
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
            <thead><tr class="bg-slate-100 text-slate-700"><th class="border border-slate-200 px-3 py-2 text-left">Examen</th><th class="border border-slate-200 px-3 py-2 text-left">Activité</th><th class="border border-slate-200 px-3 py-2">Niveau</th><th class="border border-slate-200 px-3 py-2">Phase</th><th class="border border-slate-200 px-3 py-2">Agents / jours / taux</th><th class="border border-slate-200 px-3 py-2"></th></tr></thead>
            <tbody>
            @foreach($activities as $activity)
                <tr>
                    <td class="border border-slate-200 px-3 py-2">{{ $activity['examen'] }}</td>
                    <td class="border border-slate-200 px-3 py-2">
                        <form id="activity-{{ $activity['id'] }}" method="POST" action="{{ route('vacation2026.activities.update', $activity['id']) }}" class="contents">@csrf @method('PUT')
                            <input name="libelle" value="{{ $activity['libelle'] }}" class="w-64 rounded border border-slate-300 px-2 py-1">
                        </form>
                    </td>
                    <td class="border border-slate-200 px-3 py-2"><select form="activity-{{ $activity['id'] }}" name="level" class="rounded border border-slate-300 px-2 py-1">@foreach($availableLevels as $value => $label)<option value="{{ $value }}" @selected($activity['level'] === $value)>{{ $label }}</option>@endforeach</select></td>
                    <td class="border border-slate-200 px-3 py-2"><select form="activity-{{ $activity['id'] }}" name="phase" class="rounded border border-slate-300 px-2 py-1"><option value="AVANT_SESSION" @selected($activity['phase'] === 'AVANT_SESSION')>Avant session</option><option value="PENDANT_SESSION" @selected($activity['phase'] === 'PENDANT_SESSION')>Pendant session</option><option value="APRES_SESSION" @selected($activity['phase'] === 'APRES_SESSION')>Après session</option><option value="AVANT_EPREUVES_EPS" @selected($activity['phase'] === 'AVANT_EPREUVES_EPS')>Avant EPS</option><option value="PENDANT_EPREUVES_EPS" @selected($activity['phase'] === 'PENDANT_EPREUVES_EPS')>Pendant EPS</option><option value="APRES_EPREUVES_EPS" @selected($activity['phase'] === 'APRES_EPREUVES_EPS')>Après EPS</option></select></td>
                    <td class="border border-slate-200 px-3 py-2 whitespace-nowrap"><input form="activity-{{ $activity['id'] }}" type="number" min="0" name="max_agents" value="{{ $activity['max_agents'] }}" class="w-16 rounded border border-slate-300 px-1 py-1" title="Max agents"><input form="activity-{{ $activity['id'] }}" type="number" min="1" name="nb_jours" value="{{ $activity['nb_jours'] }}" class="ml-1 w-14 rounded border border-slate-300 px-1 py-1" title="Jours"><input form="activity-{{ $activity['id'] }}" type="number" min="0" step="0.01" name="taux_activite" value="{{ $activity['taux_activite'] }}" class="ml-1 w-20 rounded border border-slate-300 px-1 py-1" title="Taux"></td>
                    <td class="border border-slate-200 px-3 py-2 whitespace-nowrap"><button form="activity-{{ $activity['id'] }}" class="rounded bg-slate-900 px-2 py-1 text-xs text-white">Enregistrer</button><form method="POST" action="{{ route('vacation2026.activities.destroy', $activity['id']) }}" class="inline" onsubmit="return confirm('Supprimer cette activité ?');">@csrf @method('DELETE')<button class="ml-1 rounded border border-red-200 bg-red-50 px-2 py-1 text-xs text-red-700">Supprimer</button></form></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
