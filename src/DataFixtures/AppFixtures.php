<?php
namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    const NUM_USERS = 10;  
    const NUM_POSTS = 50;   
    const NUM_TAGS = 5;      
    const PASSWORD = 'password';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $admin = new User();
        $admin->setUsername('admin')
              ->setEmail('admin@test.fr')
              ->setPlainPassword(self::PASSWORD);
              $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        for ($i = 0; $i < self::NUM_USERS; $i++) {
            $user = new User();
            $user->setUsername($faker->userName)
                 ->setEmail($faker->email)
                 ->setPlainPassword(self::PASSWORD);
            $manager->persist($user);
        }
        $manager->flush();

        $tags = [];
        for ($i = 0; $i < self::NUM_TAGS; $i++) {
            $tag = new Tag();
            $tag->setName($faker->word);
            $manager->persist($tag);
            $tags[] = $tag;
        }

        for ($i = 0; $i < self::NUM_POSTS; $i++) {
            $post = new Post();
            $post->setContent($faker->text)
                 ->setCreator($faker->randomElement($manager->getRepository(User::class)->findAll())) 
                 ->addTag($faker->randomElement($tags)) 
                 ->addTag($faker->randomElement($tags))
                 ->setCreatedAt(new \DateTimeImmutable()); 
            $manager->persist($post);
        }

        $manager->flush();
    }
}
