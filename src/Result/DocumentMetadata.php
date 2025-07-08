<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Result;

/**
 * Represents document metadata extracted during conversion.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class DocumentMetadata
{
    /**
     * @param array<string, mixed> $customFields
     * @param array<string> $keywords
     */
    public function __construct(
        private readonly ?string $title = null,
        private readonly ?string $author = null,
        private readonly ?\DateTimeInterface $date = null,
        private readonly array $keywords = [],
        private readonly array $customFields = [],
    ) {}

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @return array<string>
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    public function hasField(string $name): bool
    {
        return array_key_exists($name, $this->customFields);
    }

    public function getField(string $name): mixed
    {
        return $this->customFields[$name] ?? null;
    }

    /**
     * Create metadata from array (e.g., from YAML frontmatter).
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $title = $data['title'] ?? null;
        if (!is_string($title) && !is_null($title)) {
            throw new \InvalidArgumentException('Title must be a string or null.');
        }
        $author = $data['author'] ?? null;
        if (!is_string($author) && !is_null($author)) {
            throw new \InvalidArgumentException('Author must be a string or null.');
        }

        $date = null;
        if (isset($data['date'])) {
            if ($data['date'] instanceof \DateTimeInterface) {
                $date = $data['date'];
            } elseif (is_string($data['date'])) {
                try {
                    $date = new \DateTime($data['date']);
                } catch (\Exception) {
                    // Invalid date format, skip
                }
            }
        }

        $keywords = [];
        if (isset($data['keywords'])) {
            $keywords = $data['keywords'];
            if (\is_string($keywords)) {
                $keywords = explode(',', $keywords);
            }
            if (!\is_array($keywords)) {
                throw new \InvalidArgumentException('Keywords must be a string or an array.');
            }
            $words = [];
            foreach ($keywords as $keyword) {
                if (is_string($keyword)) {
                    $words[] = trim($keyword);
                } elseif ($keyword instanceof \Stringable) {
                    $words[] = (string) $keyword;
                }
            }
            $keywords = $words;
        }

        // Remove known fields from custom fields
        $customFields = array_diff_key($data, array_flip(['title', 'author', 'date', 'keywords']));

        return new self($title, $author, $date, $keywords, $customFields);
    }

    /**
     * Convert to array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->title !== null) {
            $data['title'] = $this->title;
        }

        if ($this->author !== null) {
            $data['author'] = $this->author;
        }

        if ($this->date !== null) {
            $data['date'] = $this->date->format('Y-m-d');
        }

        if (!empty($this->keywords)) {
            $data['keywords'] = $this->keywords;
        }

        return array_merge($data, $this->customFields);
    }

    /**
     * Create a new instance with updated fields.
     */
    public function withTitle(?string $title): self
    {
        return new self($title, $this->author, $this->date, $this->keywords, $this->customFields);
    }

    public function withAuthor(?string $author): self
    {
        return new self($this->title, $author, $this->date, $this->keywords, $this->customFields);
    }

    public function withDate(?\DateTimeInterface $date): self
    {
        return new self($this->title, $this->author, $date, $this->keywords, $this->customFields);
    }

    /**
     * @param array<string> $keywords
     */
    public function withKeywords(array $keywords): self
    {
        return new self($this->title, $this->author, $this->date, $keywords, $this->customFields);
    }

    public function withCustomField(string $name, mixed $value): self
    {
        $customFields = $this->customFields;
        $customFields[$name] = $value;

        return new self($this->title, $this->author, $this->date, $this->keywords, $customFields);
    }

    public function withoutCustomField(string $name): self
    {
        $customFields = $this->customFields;
        unset($customFields[$name]);

        return new self($this->title, $this->author, $this->date, $this->keywords, $customFields);
    }
}
