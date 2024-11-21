<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Throwable;

#[AsCommand(name: 'app:user:create', description: 'create user')]
class CreateUserCommand extends Command
{

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordEncoder,
        private readonly EntityManagerInterface $entityManager
    ){
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
                new InputArgument('role', InputArgument::REQUIRED, 'Role of the user'),
            ]);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = [];

        if (!$input->getArgument('username')) {
            $question = new Question('Please choose a username:');
            $question->setValidator(function ($username) {
                if (empty($username)) {
                    throw new Exception('Username can not be empty');
                }
                return $username;
            });
            $questions['username'] = $question;
        }

        if (!$input->getArgument('email')) {
            $question = new Question('Please choose an email:');
            $question->setValidator(function ($email) {
                if (empty($email)) {
                    throw new Exception('Email can not be empty');
                }
                return $email;
            });
            $questions['email'] = $question;
        }

        if (!$input->getArgument('password')) {
            $question = new Question('Please choose a password:');
            $question->setValidator(function ($password) {
                if (empty($password)) {
                    throw new Exception('Password can not be empty');
                }
                return $password;
            });
            $question->setHidden(true);
            $questions['password'] = $question;
        }

        if (!$input->getArgument('role')) {
            $question = new Question('Please choose a role:');
            $question->setValidator(function ($role) {
                if (empty($role)) {
                    throw new Exception('Role can not be empty');
                }
                return $role;
            });
            $questions['role'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $role = $input->getArgument('role');
        try {
            $userExist = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
            if($userExist){ return 0; }
            $adminUser = new User();
            $adminUser
                ->setUsername($username)
                ->setFirstName('ADMIN')
                ->setEmail($email)
                ->setRoles([$role])
                ->setIsActive(true)
                ->setPassword($this->passwordEncoder->hashPassword($adminUser, $password));

            $this->entityManager->persist($adminUser);
            $this->entityManager->flush();
            $output->writeln('<info>User Created Successfully</info>');

        } catch (Throwable $t) {
            $output->writeln('<error>'.$t->getMessage().'</error>');
        }

        return Command::SUCCESS;
    }

}