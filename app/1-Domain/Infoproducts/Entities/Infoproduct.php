<?php

namespace Promolider\Domain\Infoproducts\Entities;

use JsonSerializable;

class Infoproduct implements JsonSerializable
{
    public function __construct(
        private int $id,
        private ?string $product_type_id,
        private ?string $instructor_signature_path,
        private int $user_id,
        private ?string $id_categories,
        private ?string $title,
        private ?string $slug,
        private ?string $area,
        private ?string $description,
        private ?string $currency,
        private float $price,
        private ?string $ranking_by_user,
        private ?string $status,
        private ?string $portada,
        private ?string $url_portada,
        private ?string $course_about,
        private ?string $will_learn,
        private ?string $prev_knowledge,
        private ?string $course_for,
        private ?string $course_time,
        private ?string $course_level_id,
        private ?string $months,
        private ?string $path_url,
        private float $price_base,
        private ?string $certificate,
        private ?string $certificate_template_id,
        private ?string $marketplace_listed,
        private ?float $old_price = null,
        private ?string $language = null,
        private ?string $created_at = null,
        private ?string $updated_at = null,
        private ?string $reading_mode = null
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'product_type_id' => $this->product_type_id,
            'instructor_signature_path' => $this->instructor_signature_path,
            'user_id' => $this->user_id,
            'id_categories' => $this->id_categories,
            'title' => $this->title,
            'slug' => $this->slug,
            'area' => $this->area,
            'description' => $this->description,
            'currency' => $this->currency,
            'price' => $this->price,
            'ranking_by_user' => $this->ranking_by_user,
            'status' => $this->status,
            'portada' => $this->portada,
            'url_portada' => $this->url_portada,
            'course_about' => $this->course_about,
            'will_learn' => $this->will_learn,
            'prev_knowledge' => $this->prev_knowledge,
            'course_for' => $this->course_for,
            'course_time' => $this->course_time,
            'course_level_id' => $this->course_level_id,
            'months' => $this->months,
            'path_url' => $this->path_url,
            'price_base' => $this->price_base,
            'certificate' => $this->certificate,
            'certificate_template_id' => $this->certificate_template_id,
            'marketplace_listed' => $this->marketplace_listed,
            'old_price' => $this->old_price,
            'language' => $this->language,
            'reading_mode' => $this->readingMode(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    // Getters públicos para acceder a las propiedades
    public function getId(): int { return $this->id; }
    public function getProductTypeId(): ?string { return $this->product_type_id; }
    public function getInstructorSignaturePath(): ?string { return $this->instructor_signature_path; }
    public function getUserId(): int { return $this->user_id; }
    public function getIdCategories(): ?string { return $this->id_categories; }
    public function getTitle(): ?string { return $this->title; }
    public function getSlug(): ?string { return $this->slug; }
    public function getArea(): ?string { return $this->area; }
    public function getDescription(): ?string { return $this->description; }
    public function getCurrency(): ?string { return $this->currency; }
    public function getPrice(): float { return $this->price; }
    public function getRankingByUser(): ?string { return $this->ranking_by_user; }
    public function getStatus(): ?string { return $this->status; }
    public function getPortada(): ?string { return $this->portada; }
    public function getUrlPortada(): ?string { return $this->url_portada; }
    public function getCourseAbout(): ?string { return $this->course_about; }
    public function getWillLearn(): ?string { return $this->will_learn; }
    public function getPrevKnowledge(): ?string { return $this->prev_knowledge; }
    public function getCourseFor(): ?string { return $this->course_for; }
    public function getCourseTime(): ?string { return $this->course_time; }
    public function getCourseLevelId(): ?string { return $this->course_level_id; }
    public function getMonths(): ?string { return $this->months; }
    public function getPathUrl(): ?string { return $this->path_url; }
    public function getPriceBase(): float { return $this->price_base; }
    public function getCertificate(): ?string { return $this->certificate; }
    public function getCertificateTemplateId(): ?string { return $this->certificate_template_id; }
    public function getMarketplaceListed(): ?string { return $this->marketplace_listed; }
    public function getOldPrice(): ?float { return $this->old_price; }
    public function getLanguage(): ?string { return $this->language; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    /**
     * Modo de entrega del libro. Si no está definido se asume 'download', que
     * es como se comportaba el sistema antes de existir esta opción.
     */
    public function readingMode(): string
    {
        return $this->reading_mode === 'online' ? 'online' : 'download';
    }

    /**
     * Regla de negocio: el comprador solo puede descargar los archivos si el
     * productor eligió ese modo de entrega.
     */
    public function allowsDownload(): bool
    {
        return $this->readingMode() === 'download';
    }

    // Lógica de negocio: Verificar si el infoproducto está pendiente de aprobación
    public function pendingApproval(): bool
    {
        return $this->status === '1';   // '1' representa que el infoproducto está pendiente de aprobación
    }

    // Lógica de negocio: Verificar si el infoproducto está activo
    public function isActive(): bool
    {
        return $this->status === '2';   // '2' representa que el infoproducto está activo
    }

    // Lógica de negocio: Verificar si el infoproducto tiene observaciones
    public function hasObservations(): bool
    {
        return $this->status === '3';   // '3' representa que el infoproducto tiene observaciones
    }
}
