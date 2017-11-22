<?php

namespace GC\MainBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\EntityManager;
use GC\MainBundle\Entity\Dentist;

class ImportFromCsvCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:import-from-csv')

            // the short description shown while running "php bin/console list"
            ->setDescription('Import dentists from a csv file.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('mdr')

            ->addArgument('file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $repo = $this->getContainer()->get('doctrine')->getRepository(Dentist::class);

        $output->writeln(['Csv Import', '============', '',]);

        $output->writeln('Deleting existing objects...');
        $dentists = $repo->findAll();
        foreach ($dentists as $dentist)
        {
            $em->remove($dentist);
        }
        $em->flush();

        $file = $input->getArgument('file');

        $row = 0;
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                if ($row % 50 == 0)
                    $output->write($row . '... ');

                if ($row > 0)
                {
                    $dentist = new Dentist();

                    $dentist->setFirstname($data[1]);
                    $dentist->setLastname($data[2]);
                    $dentist->setEmail($data[3]);
                    if ($data[4] == 'Male')
                        $dentist->setGender(1);
                    else
                        $dentist->setGender(0);
                    $dentist->setAddress($data[5]);
                    $dentist->setCity($data[6]);
                    $dentist->setPhone($data[7]);
                    $dentist->setImage($data[8]);

                    $openings = json_decode($data[9]);
                    if (false && !is_null($openings))
                    {
                        $openings = $openings[0];
                        $output->writeln(print_r($openings, true));

                        if (array_key_exists('mon', $openings))
                        {
                            $dentist->setMondayOpened(1);
                            $dentist->setMondayOpening($openings['mon']['open']);
                            $dentist->setMondayOpening($openings['mon']['close']);
                        }
                        else
                        {
                            $dentist->setMondayOpened(0);
                        }

                        if (array_key_exists('tue', $openings))
                        {
                            $dentist->setTuesdayOpened(1);
                            $dentist->setTuesdayOpening($openings['tue']['open']);
                            $dentist->setTuesdayOpening($openings['tue']['close']);
                        }
                        else
                        {
                            $dentist->setTuesdayOpened(0);
                        }

                        if (array_key_exists('wed', $openings))
                        {
                            $dentist->setWednesdayOpened(1);
                            $dentist->setWednesdayOpening($openings['wed']['open']);
                            $dentist->setWednesdayOpening($openings['wed']['close']);
                        }
                        else
                        {
                            $dentist->setWednesdayOpened(0);
                        }

                        if (array_key_exists('thu', $openings))
                        {
                            $dentist->setThursdayOpened(1);
                            $dentist->setThursdayOpening($openings['thu']['open']);
                            $dentist->setThursdayOpening($openings['thu']['close']);
                        }
                        else
                        {
                            $dentist->setThursdayOpened(0);
                        }

                        if (array_key_exists('fri', $openings))
                        {
                            $dentist->setFridayOpened(1);
                            $dentist->setFridayOpening($openings['fri']['open']);
                            $dentist->setFridayOpening($openings['fri']['close']);
                        }
                        else
                        {
                            $dentist->setFridayOpened(0);
                        }

                        if (array_key_exists('sat', $openings))
                        {
                            $dentist->setSaturdayOpened(1);
                            $dentist->setSaturdayOpening($openings['sat']['open']);
                            $dentist->setSaturdayOpening($openings['sat']['close']);
                        }
                        else
                        {
                            $dentist->setSaturdayOpened(0);
                        }

                        if (array_key_exists('sun', $openings))
                        {
                            $dentist->setSundayOpened(1);
                            $dentist->setSundayOpening($openings['sun']['open']);
                            $dentist->setSundayOpening($openings['sun']['close']);
                        }
                        else
                        {
                            $dentist->setSundayOpened(0);
                        }
                    }
                    else
                    {
                        $dentist->setMondayOpened(0);
                        $dentist->setTuesdayOpened(0);
                        $dentist->setWednesdayOpened(0);
                        $dentist->setThursdayOpened(0);
                        $dentist->setFridayOpened(0);
                        $dentist->setSaturdayOpened(0);
                        $dentist->setSundayOpened(0);
                    }


                    $dentist->setSpecialty($data[10]);

                    $em->persist($dentist);
                    $em->flush();
                }

                $row++;
            }
            fclose($handle);
            $output->write("\n");
        }

        $output->writeln(['', '============', 'Done']);
    }
}