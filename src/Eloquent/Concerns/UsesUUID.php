<?php

namespace Astrotomic\LaravelEloquentUuid\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;

/**
 * @method static Builder byUuid(string|string[] $uuid)
 *
 * @mixin Model
 */
trait UsesUUID
{
    public static function bootUsesUUID(): void
    {
        self::creating(static function (Model $model): void {
            /** @var Model|UsesUUID $model */
            if (Str::isUuid($model->getUuid())) {
                return;
            }

            $model->setAttribute(
                $model->getUuidName(),
                static::generateUniqueUuid()
            );
        });
    }

    public static function generateUniqueUuid(): string
    {
        do {
            $uuid = static::generateUuid()->toString();
        } while (static::byUuid($uuid)->exists());

        return $uuid;
    }

    public static function generateUuid(): UuidInterface
    {
        return Str::uuid();
    }

    /**
     * @param Builder $query
     * @param string|string[]|UuidInterface|UuidInterface[] $uuid
     *
     * @return Builder
     */
    public function scopeByUuid(Builder $query, $uuid): Builder
    {
        if (is_string($uuid) || $uuid instanceof UuidInterface) {
            return $query->where($this->getQualifiedUuidName(), '=', strval($uuid));
        } elseif (is_array($uuid)) {
            return $query->whereIn($this->getQualifiedUuidName(), array_map('strval', $uuid));
        }

        throw new InvalidArgumentException('The UUID has to be of type string, array or null.');
    }

    public function getUuid(): ?string
    {
        return $this->getAttribute($this->getUuidName());
    }

    public function getQualifiedUuidName(): string
    {
        return $this->qualifyColumn($this->getUuidName());
    }

    public function getUuidName(): string
    {
        return $this->uuidName ?? 'uuid';
    }
}