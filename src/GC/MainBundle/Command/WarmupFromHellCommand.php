<?php

namespace GC\MainBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use GC\MainBundle\Entity\Dentist;

class WarmupFromHellCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:warmup-from-hell')
            // the short description shown while running "php bin/console list"
            ->setDescription('Warmup the cache.')
            ->addOption('dry-run')
            ->addOption('days')
            ->addOption('lol')
            ->addOption('pages')
            ->addArgument('base-url')
            ->addOption('silent')

            // the full command description shown when running the command with
            // the "--help" option
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        /** @var EntityRepository $repo */
        $repo = $this->getContainer()->get('doctrine')->getRepository(Dentist::class);

        $router = $this->getContainer()->get('router');

        $baseUrl = $input->getArgument('base-url');

        $urls = array('', $router->generate('gc_main_search'));

        $output->writeln(['Warmup form hell', '============', '',]);

        $days = ['mon', 'tue', 'wed', 'thu', 'wed'];

        $dentists = $repo->findAll();
        $query = '';
        $searchUrl = $router->generate('gc_main_search') . '?';

        /** @var Dentist $dentist */
        foreach ($dentists as $dentist)
        {
            $address = str_replace(' ', '%20', $dentist->getAddress());
            $city = str_replace(' ', '%20', $dentist->getCity());

            $urls[] = $router->generate('gc_main_detail', array('dentist_id' => $dentist->getId()));
            $urls[] = $searchUrl . 'q=' . $dentist->getFirstname();
            $urls[] = $searchUrl . 'q=' . $dentist->getLastname();
            $urls[] = $searchUrl . 'q=' . $dentist->getFirstname() . '%20' . $dentist->getLastname();
            if ($dentist->getSpecialty())
                $urls[] = $searchUrl . 'q=' . $dentist->getSpecialty();
            $urls[] = $searchUrl . 'q=' . $address;
            $urls[] = $searchUrl . 'q=' . $city;
            $urls[] = $searchUrl . 'q=' . $address . '%20' . $city;

            if ($input->getOption('days'))
            {
                foreach ($days as $day)
                {
                    $urls[] = $searchUrl . 'q=' . $dentist->getFirstname() . '&days[]=' . $day;
                    $urls[] = $searchUrl . 'q=' . $dentist->getLastname() . '&days[]=' . $day;
                    $urls[] = $searchUrl . 'q=' . $dentist->getFirstname() . '%20' . $dentist->getLastname() . '&days[]=' . $day;
                    if ($dentist->getSpecialty())
                        $urls[] = $searchUrl . 'q=' . $dentist->getSpecialty() . '&days[]=' . $day;;
                    $urls[] = $searchUrl . 'q=' . $address . '&days[]=' . $day;;
                    $urls[] = $searchUrl . 'q=' . $city . '&days[]=' . $day;;
                    $urls[] = $searchUrl . 'q=' . $address . '%20' . $city . '&days[]=' . $day;;
                }
            }
        }

        if ($input->getOption('lol'))
        {
            for ($i = 97; $i <= 122; $i++)
            {
                $urls[] = $searchUrl . 'q=' . chr($i);
                for ($j = 97; $j <= 122; $j++)
                {
                    $urls[] = $searchUrl . 'q=' . chr($i) . chr($j);
                    for ($k = 97; $k <= 122; $k++)
                    {
                        $urls[] = $searchUrl . 'q=' . chr($i) . chr($j) . chr($k);
                    }
                }
            }
        }

        $output->writeln('Start: ' . date('H:i:s'));
        $output->writeln(count($urls) . ' urls to test');

        $curl = curl_init();

        $n = 0;
        foreach ($urls as $url)
        {
            $u = $baseUrl . $url;
            $currentUrls = array($u);

            if ($input->getOption('pages') and strpos($u, 'search'))
            {
                foreach (range(2, 5) as $n)
                {
                    if (strpos($u, '?'))
                    {
                        $currentUrls[] = $baseUrl . $url . '&p=' . $n;
                    }
                    else
                    {
                        $currentUrls[] = $baseUrl . $url . '?p=' . $n;
                    }
                }
            }

            foreach ($currentUrls as $currentUrl)
            {
                $currentUrl = str_replace(' ', '%20', $currentUrl);
                if ($input->getOption('dry-run'))
                {
                    if (!$input->getOption('silent'))
                    {
                        $output->writeln($currentUrl);
                    }
                }
                else
                {
                    curl_setopt($curl, CURLOPT_URL, $currentUrl);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($curl);
                    $info = curl_getinfo($curl);
                    if (!$input->getOption('silent'))
                    {
                        $output->writeln($currentUrl . '... ' . $info['http_code']);
                    }
                }
            }

            if ($n % 500 == 0)
            {
                $output->write($n . '... ');
            }
            $n++;
        }


        curl_close($curl);

        $output->writeln('');

        $output->writeln('End: ' . date('H:i:s'));

        $output->writeln(count($urls) . ' urls generated.');

        $output->writeln(['', '============', 'Done']);
    }
}
