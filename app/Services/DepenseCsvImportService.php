<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Categorie;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class DepenseCsvImportService
{
    private const MAX_ROWS = 500;

    private const MAX_BYTES = 524288;

    /**
     * @return array{imported: int, skipped: int, errors: list<string>}
     */
    public function import(User $user, UploadedFile $file, int $mois, int $annee): array
    {
        if ($file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'csv' => 'Le fichier dépasse 512 Ko.',
            ]);
        }

        $content = file_get_contents($file->getRealPath());
        if ($content === false) {
            throw ValidationException::withMessages(['csv' => 'Fichier illisible.']);
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $lines = preg_split('/\r\n|\r|\n/', trim($content)) ?: [];

        if (count($lines) < 2) {
            throw ValidationException::withMessages(['csv' => 'Le fichier est vide ou sans données.']);
        }

        $categories = Categorie::where('user_id', $user->id)
            ->where('is_active', true)
            ->get()
            ->keyBy(fn (Categorie $c) => mb_strtolower(trim($c->nom)));

        $budget = Budget::firstOrCreate(
            ['user_id' => $user->id, 'mois' => $mois, 'annee' => $annee],
            ['salaire_fixe' => 0, 'solde_charges' => 0, 'epargne_objectif' => 0]
        );

        $imported = 0;
        $skipped  = 0;
        $errors   = [];
        $dataRows = 0;

        foreach ($lines as $index => $line) {
            $lineNum = $index + 1;
            $line = trim($line);

            if ($line === '' || str_starts_with(mb_strtoupper($line), 'EMPRUNTS')
                || str_starts_with(mb_strtoupper($line), 'REMBOURSEMENTS')) {
                break;
            }

            $cols = str_getcsv($line, ';');
            if (count($cols) < 4) {
                continue;
            }

            $first = mb_strtolower(trim($cols[0]));
            if ($first === 'date' || $first === '') {
                continue;
            }

            if (isset($cols[2]) && mb_strtoupper(trim($cols[2])) === 'TOTAL') {
                continue;
            }

            $dataRows++;
            if ($dataRows > self::MAX_ROWS) {
                throw ValidationException::withMessages([
                    'csv' => 'Maximum ' . self::MAX_ROWS . ' lignes de dépenses.',
                ]);
            }

            $dateRaw   = trim($cols[0]);
            $catNom    = trim($cols[1]);
            $note      = isset($cols[2]) ? trim($cols[2]) : '';
            $montantRaw = trim($cols[3] ?? '');
            $imprevue  = isset($cols[4]) && in_array(mb_strtolower(trim($cols[4])), ['oui', '1', 'true', 'yes'], true);

            try {
                $date = $this->parseDate($dateRaw);
            } catch (\InvalidArgumentException $e) {
                $errors[] = "Ligne {$lineNum} : date invalide.";
                $skipped++;
                continue;
            }

            if (! BudgetPeriodService::dateDansPeriode($user, $date, $mois, $annee)) {
                $errors[] = "Ligne {$lineNum} : date hors de la période sélectionnée.";
                $skipped++;
                continue;
            }

            $catKey = mb_strtolower($catNom);
            $categorie = $categories->get($catKey);
            if (! $categorie) {
                $errors[] = "Ligne {$lineNum} : catégorie « {$catNom} » introuvable.";
                $skipped++;
                continue;
            }

            $montant = (int) preg_replace('/\s+/', '', $montantRaw);
            if ($montant < 1) {
                $errors[] = "Ligne {$lineNum} : montant invalide.";
                $skipped++;
                continue;
            }

            $budget->depenses()->create([
                'categorie_id' => $categorie->id,
                'montant'      => $montant,
                'date'         => $date,
                'note'         => $note !== '' ? mb_substr($note, 0, 255) : null,
                'imprevue'     => $imprevue,
            ]);
            $imported++;
        }

        if ($imported > 0) {
            AlerteService::analyserBudget($user, $budget->fresh());
        }

        return compact('imported', 'skipped', 'errors');
    }

    private function parseDate(string $raw): Carbon
    {
        $raw = trim($raw);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return Carbon::parse($raw)->startOfDay();
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $raw, $m)) {
            return Carbon::createFromDate((int) $m[3], (int) $m[2], (int) $m[1])->startOfDay();
        }

        throw new \InvalidArgumentException('invalid');
    }
}
