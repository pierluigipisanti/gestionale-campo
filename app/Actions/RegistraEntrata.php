<?php

namespace App\Actions;

use App\Models\Accesso;

// Registra l'entrata al varco di chi transita (VVF, ENEL, polizia, fornitori...).
// Cognome/nome/CF separati come al check-in; compilabili col lettore documenti.
class RegistraEntrata
{
    /**
     * @param  array  $dati  chiavi: cognome (obbligatoria), nome, codice_fiscale,
     *                       categoria_id, ente_appartenenza, documento, targa_veicolo, motivo
     */
    public function __invoke(int $campoId, array $dati, ?int $operatoreId = null): Accesso
    {
        return Accesso::create([
            'campo_id'          => $campoId,
            'categoria_id'      => $dati['categoria_id'] ?? null,
            'cognome'           => $dati['cognome'],
            'nome'              => $dati['nome'] ?? null,
            'codice_fiscale'    => $dati['codice_fiscale'] ?? null,
            'telefono'          => $dati['telefono'] ?? null,
            'ente_appartenenza' => $dati['ente_appartenenza'] ?? null,
            'documento'         => $dati['documento'] ?? null,
            'targa_veicolo'     => $dati['targa_veicolo'] ?? null,
            'motivo'            => $dati['motivo'] ?? null,
            'entrata_at'        => now(),
            'operatore_id'      => $operatoreId,
        ]);
    }
}
