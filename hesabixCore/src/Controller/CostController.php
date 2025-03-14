<?php

namespace App\Controller;

use App\Service\Access;
use App\Service\Jdate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CostController extends AbstractController
{
    #[Route('/api/cost/dashboard/data', name: 'app_cost_dashboard_data', methods: ['GET'])]
    public function getCostDashboardData(
        Request $request,
        Access $access,
        Jdate $jdate,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // بررسی دسترسی کاربر
        $acc = $access->hasRole('cost');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        // تاریخ‌های شمسی برای امروز، هفته و ماه
        $today = $jdate->jdate('Y/m/d', time());
        $weekStart = $jdate->jdate('Y/m/d', strtotime('-6 days'));
        $monthStart = $jdate->jdate('Y/m/01', time());

        // تاریخ شروع و پایان سال مالی از $acc['year'] و تبدیل به شمسی
        $yearStartUnix = (int) $acc['year']->getStart();
        $yearEndUnix = (int) $acc['year']->getEnd();
        $yearStart = $jdate->jdate('Y/m/d', $yearStartUnix);
        $yearEnd = $jdate->jdate('Y/m/d', $yearEndUnix);

        // کوئری پایه - فقط جمع bd را محاسبه می‌کنیم
        $qb = $entityManager->createQueryBuilder()
            ->select('SUM(COALESCE(r.bd, 0)) as total')
            ->from('App\Entity\HesabdariDoc', 'd')
            ->join('d.hesabdariRows', 'r')
            ->where('d.bid = :bid')
            ->andWhere('d.money = :money')
            ->andWhere('d.type = :type')
            ->andWhere('d.year = :year')
            ->setParameter('bid', $acc['bid'])
            ->setParameter('money', $acc['money'])
            ->setParameter('type', 'cost')
            ->setParameter('year', $acc['year']);

        // هزینه امروز
        $todayCost = (clone $qb)
            ->andWhere('d.date = :date')
            ->setParameter('date', $today)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // هزینه این هفته
        $weekCost = (clone $qb)
            ->andWhere('d.date BETWEEN :start AND :end')
            ->setParameter('start', $weekStart)
            ->setParameter('end', $today)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // هزینه این ماه
        $monthCost = (clone $qb)
            ->andWhere('d.date BETWEEN :start AND :end')
            ->setParameter('start', $monthStart)
            ->setParameter('end', $today)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // هزینه سال مالی
        $yearCost = (clone $qb)
            ->andWhere('d.date BETWEEN :start AND :end')
            ->setParameter('start', $yearStart)
            ->setParameter('end', $yearEnd)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return $this->json([
            'today' => (int) $todayCost,
            'week' => (int) $weekCost,
            'month' => (int) $monthCost,
            'year' => (int) $yearCost,
        ]);
    }

    #[Route('/api/cost/top-centers', name: 'app_cost_top_centers', methods: ['GET'])]
    public function getTopCostCenters(
        Request $request,
        Access $access,
        Jdate $jdate,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // بررسی دسترسی کاربر
        $acc = $access->hasRole('cost');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        // پارامترهای درخواست
        $period = $request->query->get('period', 'today'); // پیش‌فرض: امروز
        $limit = (int) $request->query->get('limit', 10); // پیش‌فرض: 10

        // تاریخ‌های شمسی
        $today = $jdate->jdate('Y/m/d', time());
        $monthStart = $jdate->jdate('Y/m/01', time());
        $yearStartUnix = (int) $acc['year']->getStart();
        $yearEndUnix = (int) $acc['year']->getEnd();
        $yearStart = $jdate->jdate('Y/m/d', $yearStartUnix);
        $yearEnd = $jdate->jdate('Y/m/d', $yearEndUnix);

        // تنظیم بازه زمانی بر اساس فیلتر
        $dateCondition = match ($period) {
            'month' => 'd.date BETWEEN :start AND :end',
            'year' => 'd.date BETWEEN :start AND :end',
            'today' => 'd.date = :date',
            default => 'd.date = :date',
        };

        $parameters = [
            'bid' => $acc['bid'],
            'money' => $acc['money'],
            'type' => 'cost',
            'year' => $acc['year'],
        ];

        if ($period === 'month') {
            $parameters['start'] = $monthStart;
            $parameters['end'] = $today;
        } elseif ($period === 'year') {
            $parameters['start'] = $yearStart;
            $parameters['end'] = $yearEnd;
        } else { // today
            $parameters['date'] = $today;
        }

        // کوئری برای محاسبه مراکز هزینه برتر
        $qb = $entityManager->createQueryBuilder()
            ->select('t.name AS center_name, SUM(COALESCE(r.bd, 0)) AS total_cost')
            ->from('App\Entity\HesabdariDoc', 'd')
            ->join('d.hesabdariRows', 'r')
            ->join('r.ref', 't') // ارتباط با HesabdariTable از طریق ref
            ->where('d.bid = :bid')
            ->andWhere('d.money = :money')
            ->andWhere('d.type = :type')
            ->andWhere('d.year = :year')
            ->andWhere('t.type = :tableType') // فقط مراکز هزینه
            ->andWhere($dateCondition)
            ->groupBy('t.id, t.name')
            ->orderBy('total_cost', 'DESC')
            ->setParameters($parameters + ['tableType' => 'cost_center']); // فرض بر اینکه نوع مرکز هزینه 'cost_center' است
            // اگر نوع دیگری در HesabdariTable برای مراکز هزینه استفاده شده، باید جایگزین شود

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        $results = $qb->getQuery()->getResult();

        // آماده‌سازی داده‌ها برای نمودار
        $labels = [];
        $series = [];
        foreach ($results as $row) {
            $labels[] = $row['center_name'];
            $series[] = (int) $row['total_cost'];
        }

        return $this->json([
            'labels' => $labels,
            'series' => $series,
        ]);
    }
}