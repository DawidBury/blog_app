<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\User;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use phpDocumentor\Reflection\Types\Self_;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private const USERS = [
        [
            'username' => 'admin',
            'email' => 'admin@blog.com',
            'name' => 'Dawid Bury',
            'password' => 'secret123!',
            'roles' => [User::ROLE_SUPERADMIN]
        ],
        [
            'username' => 'john_doe',
            'email' => 'john@blog.com',
            'name' => 'John Doe',
            'password' => 'secret123!',
            'roles' => [User::ROLE_ADMIN]
        ],
        [
            'username' => 'rob_smith',
            'email' => 'admin@blog.com',
            'name' => 'Rob Smith',
            'password' => 'secret123!',
            'roles' => [User::ROLE_WRITER]
        ],
        [
            'username' => 'jenny_rowling',
            'email' => 'jenny@blog.com',
            'name' => 'Jenny Rowling',
            'password' => 'secret123!',
            'roles' => [User::ROLE_WRITER]
        ],
        [
            'username' => 'han_solo',
            'email' => 'han@blog.com',
            'name' => 'Han Solo',
            'password' => 'secret123!',
            'roles' => [User::ROLE_EDITOR]
        ],
        [
            'username' => 'jedi_knight',
            'email' => 'jedi@blog.com',
            'name' => 'Jedi Knight',
            'password' => 'secret123!',
            'roles' => [User::ROLE_COMMENTATOR]
        ]
    ];

    private $passwordEncoder;

    private $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = \Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
        $this->loadComments($manager);
    }

    public function loadBlogPosts(ObjectManager $manager)
    {
        for($i=0; $i<100; $i++)
        {
            $blogPost = new BlogPost();
            $blogPost->setTitle($this->faker->realText(30));
            $blogPost->setPublished(($this->faker->dateTime));
            $blogPost->setContent($this->faker->realText());
            $author = $this->getRandomUserReference($blogPost);
            $blogPost->setAuthor($author);
            $blogPost->setSlug($this->faker->slug);

            $this->setReference("blog_post_$i", $blogPost);

            $manager->persist($blogPost);
        }

        $manager->flush();
    }

    public function loadComments(ObjectManager $manager)
    {
        for($i=0; $i<100; $i++)
        {
            for($j=0; $j<rand(1, 10); $j++)
            {
                $comment = new Comment();
                $comment->setContent($this->faker->realText());
                $comment->setPublished($this->faker->dateTimeThisYear());
                $author = $this->getRandomUserReference($comment);
                $comment->setAuthor($author);
                $comment->setBlogPost($this->getReference("blog_post_$i"));

                $manager->persist($comment);
            }
        }
        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager)
    {
        foreach (self::USERS as $userFixture) {
            $user = new User();
            $user->setUsername($userFixture['username']);
            $user->setEmail($userFixture['email']);
            $user->setName($userFixture['name']);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $userFixture['password']
            ));
            $user->setRoles($userFixture['roles']);
            $this->addReference('user_' . $userFixture['username'], $user);
            $manager->persist($user);
        }
        $manager->flush();
    }

    /**
     * @param $entity
     * @return User
     */
    public function getRandomUserReference($entity): User
    {
        $randomUser = self::USERS[rand(0, 5)];

        if ($entity instanceof BlogPost && !count(array_intersect($randomUser['roles'], [User::ROLE_SUPERADMIN, User::ROLE_ADMIN, User::ROLE_WRITER]))) {
            return $this->getRandomUserReference($entity);
        }

        if ($entity instanceof Comment && !count(
            array_intersect(
                $randomUser['roles'],
                [
                    User::ROLE_SUPERADMIN,
                    User::ROLE_ADMIN,
                    User::ROLE_WRITER,
                    User::ROLE_COMMENTATOR
                ]
            )
        )) {
            return $this->getRandomUserReference($entity);
        }
        return $this->getReference('user_' . $randomUser['username']);
    }
}
