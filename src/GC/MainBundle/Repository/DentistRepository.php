<?php

namespace GC\MainBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * DentistRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DentistRepository extends \Doctrine\ORM\EntityRepository
{
    const RESULTS_PER_PAGE = 20;

    public function searchFromCriteria($memcache, $searchQuery, $page = 1, $openDays = array(), $openHour = null)
    {
        $openDaysArray = implode(';', $openDays);
        $cacheKey = $searchQuery . '-' . $page . '-' . $openDaysArray . '-' . $openHour;

        $cachedValue = $memcache->get($cacheKey);

        if ($cachedValue === false) {

            $qb = $this->createQueryBuilder('d');
            $qb->where($qb->expr()->like('d.firstname', '?1'));
            $qb->orWhere($qb->expr()->like('d.lastname', '?1'));
            $qb->orWhere($qb->expr()->like('d.address', '?1'));
            $qb->orWhere($qb->expr()->like('d.city', '?1'));
            $qb->orWhere($qb->expr()->like('d.specialty', '?1'));

            $qb->setParameter(1, "%$searchQuery%");

            if ($openDays) {
                $conditions = $qb->expr()->orX();
                foreach ($openDays as $key => $openDay) {
                    $condition = $qb->expr()->andX(
                        $qb->expr()->isNotNull("d.${openDay}Opening"),
                        $qb->expr()->isNotNull("d.${openDay}Closing")
                    );

                    if ($openHour) {
                        $condition->add($qb->expr()->andX(
                            $qb->expr()->lte("d.${openDay}Opening", '?2'),
                            $qb->expr()->gte("d.${openDay}Closing", '?2')
                        ));

                        $qb->setParameter(2, $openHour);
                    }

                    $conditions->add($condition);
                }
                $qb->andWhere($conditions);
            }

            $paginator = $this->_paginate($qb, $page);

            $results = $paginator->getIterator()->getArrayCopy();
            $totalResultsCount = count($qb->getQuery()->getResult());

            $memcache->set($cacheKey, $results, 0, 345600);
            $memcache->set($cacheKey . '-count', $totalResultsCount, 0, 345600);

        } else {
            return $cachedValue;
        }

        return $results;
    }

    /**
     * Paginator Helper
     *
     * Pass through a query object, current page & limit
     * the offset is calculated from the page and limit
     * returns an `Paginator` instance, which you can call the following on:
     *
     *     $paginator->getIterator()->count() # Total fetched (ie: `5` posts)
     *     $paginator->count() # Count of ALL posts (ie: `20` posts)
     *     $paginator->getIterator() # ArrayIterator
     *
     * @param Doctrine\ORM\Query $dql   DQL Query Object
     * @param integer            $page  Current page (defaults to 1)
     * @param integer            $limit The total number per page (defaults to 20)
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    protected function _paginate($dql, $page = 1, $limit = self::RESULTS_PER_PAGE)
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setHydrationMode(\Doctrine\ORM\Query::HYDRATE_ARRAY)
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }
}
