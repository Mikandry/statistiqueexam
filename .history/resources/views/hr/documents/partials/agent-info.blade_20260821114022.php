<div class="agent-info">

    <table>

        <tr>
            <td width="30%" class="label">
                Nom et Prénoms :
            </td>
            <td>
                <strong>{{ $agent->full_name }}</strong>
            </td>
        </tr>

        <tr>
            <td class="label">
                IM :
            </td>
            <td>
                {{ $agent->matricule ?: '—' }}
            </td>
        </tr>

        <tr>
            <td class="label">
                Corps et Grade :
            </td>
            <td>
                {{ $agent->corps ?: '—' }}
                /
                {{ $agent->grade ?: '—' }}
            </td>
        </tr>

        <tr>
            <td class="label">
                Budget :
            </td>
            <td>
                {{ $agent->budget ?: '—' }}
                &nbsp;&nbsp;
                <strong>Chapitre :</strong>
                {{ $agent->chapitre ?: '—' }}
            </td>
        </tr>

        <tr>
            <td class="label">
                Direction :
            </td>
            <td>
                {{ $agent->direction ?: '—' }}
            </td>
        </tr>

        <tr>
            <td class="label">
                Service :
            </td>
            <td>
                {{ $agent->service ?: '—' }}
            </td>
        </tr>

        <tr>
            <td class="label">
                Fonction :
            </td>
            <td>
                {{ $agent->fonction ?: '—' }}
            </td>
        </tr>

    </table>

</div>