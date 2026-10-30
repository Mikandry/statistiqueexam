<?php

namespace Tests\Feature;

use App\Models\Dren;
use App\Models\Province;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportProvinceTest extends TestCase
{
    public function test_l_import_general_cree_et_relie_la_province_a_la_dren(): void
    {
        $this->withoutVite()->withoutMiddleware();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $csv = "dren;province;cisco;centre_correction;centre_ecrit\n"
            ."ANALAMANGA;Antananarivo;CISCO A;EPP X;EPP X\n"
            ."BOENY;Mahajanga;CISCO B;CEG Y;CEG Y\n";
        $file = UploadedFile::fake()->createWithContent('r.csv', $csv);
        $this->actingAs($admin)->post(route('imports.references'), ['type_examen' => 'BEPC', 'references_file' => $file])->assertRedirect();

        $d = Dren::where('nom', 'ANALAMANGA')->first();
        $this->assertNotNull($d->province_id);
        $this->assertSame(Province::find($d->province_id)->nom, $d->province);
        $this->assertNotNull(Dren::where('nom', 'BOENY')->first()->province_id);

        // Réimport avec une autre province : la DREN est re-rattachée, pas dupliquée.
        $csv2 = "dren;province\nANALAMANGA;Toamasina\n";
        $this->actingAs($admin)->post(route('imports.drens'), ['drens_file' => UploadedFile::fake()->createWithContent('d.csv', $csv2)]);
        $this->assertSame(1, Dren::where('nom', 'ANALAMANGA')->count());
        $this->assertStringContainsStringIgnoringCase('Toamasina', Dren::where('nom', 'ANALAMANGA')->value('province'));
    }
}
