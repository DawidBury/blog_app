<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BlogPostRepository")
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *          "id": "exact",
 *          "title": "partial",
 *          "content": "partial",
 *          "author": "exact",
 *          "author.name": "partial"
 *     }
 * )
 * @ApiFilter(
 *     DateFilter::class,
 *     properties={
 *          "published"
 *     }
 * )
 * //available gt,gte,lt,lte
 * @ApiFilter(
 *     RangeFilter::class,
 *     properties={
 *          "id"
 *     }
 * )
 * @ApiFilter(
 *     OrderFilter::class,
 *     properties={
 *          "id",
 *          "published",
 *          "title"
 *     }
 * )
 * @ApiFilter(
 *     PropertyFilter::class,
 *     arguments={
 *          "parameterName": "properties",
 *          "overrideDefaultProperties": false,
 *          "whitelist": {"id", "author", "slug", "title"}
 *     }
 * )
 * @ApiResource(
 *     attributes={"order"={"published": "DESC"}},
 *     itemOperations={
 *          "get"= {
 *              "normalization_context"={"groups"={"get-blog-post-with-author"}}
 *          },
 *          "put" = {
 *              "access_control" = "is_granted('ROLE_EDITOR') or (is_granted('ROLE_WRITER') and object.getAuthor() === user)"
 *          }
 *     },
 *     collectionOperations={
 *          "get",
 *          "post" = {
 *              "access_control" = "is_granted('ROLE_WRITER')"
 *          }
 *     }, denormalizationContext={
 *          "groups"={"post"}
 *     }
 * )
 */
class BlogPost implements AuthoredEntityInterface, PublishedDateEntityInterface
{


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get-blog-post-with-author"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"post", "get-blog-post-with-author"})
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"get-blog-post-with-author"})
     */
    private $published;

    /**
     * @ORM\Column(type="text")
     * @Groups({"post", "get-blog-post-with-author"})
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get-blog-post-with-author"})
     */
    private $author;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"post", "get-blog-post-with-author"})
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="blogPost")
     * @ApiSubresource()
     * @Groups({"get-blog-post-with-author"})
     */
    private $comments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Upload")
     * @ORM\JoinTable()
     * @ApiSubresource()
     * @Groups({"post", "get-blog-post-with-author"})
     */
    private $uploads;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->uploads = new ArrayCollection();
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPublished(): ?\DateTime
    {
        return $this->published;
    }

    public function setPublished(\DateTimeInterface $published): PublishedDateEntityInterface
    {
        $this->published = $published;

        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @param UserInterface $author
     * @return AuthoredEntityInterface
     */
    public function setAuthor(UserInterface $author): AuthoredEntityInterface
    {
        $this->author = $author;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content): void
    {
        $this->content = $content;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug): void
    {
        $this->slug = $slug;
    }

    public function getUploads()
    {
        return $this->uploads;
    }

    public function addUpload(Upload $upload)
    {
        $this->uploads->add($upload);
    }

    public function removeUpload(Upload $upload)
    {
        $this->uploads->removeElement($upload);
    }
}
