<?php

declare(strict_types=1);

namespace Fleet\Domain;

/**
 * Configuration des correspondances de tailles de cadre.
 * Permet de définir des plages personnalisées pour chaque taille (XS, S, M, L, XL, XXL).
 */
final class SizeMappingConfiguration
{
    public function __construct(
        private readonly string $id,
        private readonly int $version,
        private bool $isActive,
        private readonly SizeRange $xsCm,
        private readonly SizeRange $xsInch,
        private readonly SizeRange $sCm,
        private readonly SizeRange $sInch,
        private readonly SizeRange $mCm,
        private readonly SizeRange $mInch,
        private readonly SizeRange $lCm,
        private readonly SizeRange $lInch,
        private readonly SizeRange $xlCm,
        private readonly SizeRange $xlInch,
        private readonly SizeRange $xxlCm,
        private readonly SizeRange $xxlInch,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * Crée une configuration avec les valeurs par défaut.
     */
    public static function createDefault(string $id, int $version = 1): self
    {
        $now = new \DateTimeImmutable();

        return new self(
            id: $id,
            version: $version,
            isActive: true,
            xsCm: new SizeRange(48, 50),
            xsInch: new SizeRange(13, 14),
            sCm: new SizeRange(51, 53),
            sInch: new SizeRange(15, 16),
            mCm: new SizeRange(54, 56),
            mInch: new SizeRange(17, 18),
            lCm: new SizeRange(57, 59),
            lInch: new SizeRange(19, 20),
            xlCm: new SizeRange(60, 62),
            xlInch: new SizeRange(21, 22),
            xxlCm: new SizeRange(63, 999),
            xxlInch: new SizeRange(23, 999),
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function xsCm(): SizeRange
    {
        return $this->xsCm;
    }

    public function xsInch(): SizeRange
    {
        return $this->xsInch;
    }

    public function sCm(): SizeRange
    {
        return $this->sCm;
    }

    public function sInch(): SizeRange
    {
        return $this->sInch;
    }

    public function mCm(): SizeRange
    {
        return $this->mCm;
    }

    public function mInch(): SizeRange
    {
        return $this->mInch;
    }

    public function lCm(): SizeRange
    {
        return $this->lCm;
    }

    public function lInch(): SizeRange
    {
        return $this->lInch;
    }

    public function xlCm(): SizeRange
    {
        return $this->xlCm;
    }

    public function xlInch(): SizeRange
    {
        return $this->xlInch;
    }

    public function xxlCm(): SizeRange
    {
        return $this->xxlCm;
    }

    public function xxlInch(): SizeRange
    {
        return $this->xxlInch;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Récupère la plage pour une taille donnée.
     */
    public function getRangeForSize(FrameSizeLetter $letter, string $unit): SizeRange
    {
        return match ([$letter, $unit]) {
            [FrameSizeLetter::XS, 'cm'] => $this->xsCm,
            [FrameSizeLetter::XS, 'inch'] => $this->xsInch,
            [FrameSizeLetter::S, 'cm'] => $this->sCm,
            [FrameSizeLetter::S, 'inch'] => $this->sInch,
            [FrameSizeLetter::M, 'cm'] => $this->mCm,
            [FrameSizeLetter::M, 'inch'] => $this->mInch,
            [FrameSizeLetter::L, 'cm'] => $this->lCm,
            [FrameSizeLetter::L, 'inch'] => $this->lInch,
            [FrameSizeLetter::XL, 'cm'] => $this->xlCm,
            [FrameSizeLetter::XL, 'inch'] => $this->xlInch,
            [FrameSizeLetter::XXL, 'cm'] => $this->xxlCm,
            [FrameSizeLetter::XXL, 'inch'] => $this->xxlInch,
            default => throw new \InvalidArgumentException('Invalid size or unit'),
        };
    }

    /**
     * Calcule la taille lettre à partir d'une valeur numérique.
     */
    public function letterFromNumeric(int|float $value, string $unit): FrameSizeLetter
    {
        foreach (FrameSizeLetter::cases() as $letter) {
            $range = $this->getRangeForSize($letter, $unit);
            if ($range->contains($value)) {
                return $letter;
            }
        }

        // Si aucune correspondance, retourner XXL
        return FrameSizeLetter::XXL;
    }

    /**
     * Crée une nouvelle version de la configuration avec les plages mises à jour.
     */
    public function update(
        SizeRange $xsCm,
        SizeRange $xsInch,
        SizeRange $sCm,
        SizeRange $sInch,
        SizeRange $mCm,
        SizeRange $mInch,
        SizeRange $lCm,
        SizeRange $lInch,
        SizeRange $xlCm,
        SizeRange $xlInch,
        SizeRange $xxlCm,
        SizeRange $xxlInch,
    ): self {
        // Désactiver la configuration actuelle
        $this->deactivate();

        // Créer une nouvelle version
        return new self(
            id: \Ramsey\Uuid\Uuid::uuid4()->toString(),
            version: $this->version + 1,
            isActive: true,
            xsCm: $xsCm,
            xsInch: $xsInch,
            sCm: $sCm,
            sInch: $sInch,
            mCm: $mCm,
            mInch: $mInch,
            lCm: $lCm,
            lInch: $lInch,
            xlCm: $xlCm,
            xlInch: $xlInch,
            xxlCm: $xxlCm,
            xxlInch: $xxlInch,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'is_active' => $this->isActive,
            'sizes' => [
                [
                    'letter' => FrameSizeLetter::XS->value,
                    'label' => FrameSizeLetter::XS->label(),
                    'cm' => $this->xsCm->toArray(),
                    'inch' => $this->xsInch->toArray(),
                ],
                [
                    'letter' => FrameSizeLetter::S->value,
                    'label' => FrameSizeLetter::S->label(),
                    'cm' => $this->sCm->toArray(),
                    'inch' => $this->sInch->toArray(),
                ],
                [
                    'letter' => FrameSizeLetter::M->value,
                    'label' => FrameSizeLetter::M->label(),
                    'cm' => $this->mCm->toArray(),
                    'inch' => $this->mInch->toArray(),
                ],
                [
                    'letter' => FrameSizeLetter::L->value,
                    'label' => FrameSizeLetter::L->label(),
                    'cm' => $this->lCm->toArray(),
                    'inch' => $this->lInch->toArray(),
                ],
                [
                    'letter' => FrameSizeLetter::XL->value,
                    'label' => FrameSizeLetter::XL->label(),
                    'cm' => $this->xlCm->toArray(),
                    'inch' => $this->xlInch->toArray(),
                ],
                [
                    'letter' => FrameSizeLetter::XXL->value,
                    'label' => FrameSizeLetter::XXL->label(),
                    'cm' => $this->xxlCm->toArray(),
                    'inch' => $this->xxlInch->toArray(),
                ],
            ],
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
