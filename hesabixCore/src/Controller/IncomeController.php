<?php

namespace App\Controller;

use App\Service\Access;
use App\Service\Jdate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;

class IncomeController extends AbstractController
{
    #[Route('/api/income/dashboard/data', name: 'app_income_dashboard_data', methods: ['GET'])]
    public function getIncomeDashboardData(
        Request $request,
        Access $access,
        Jdate $jdate,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // بررسی دسترسی کاربر
        $acc = $access->hasRole('income');
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

        // کوئری پایه - جمع bs را محاسبه می‌کنیم
        $qb = $entityManager->createQueryBuilder()
            ->select('SUM(COALESCE(r.bs, 0)) as total')
            ->from('App\Entity\HesabdariDoc', 'd')
            ->join('d.hesabdariRows', 'r')
            ->where('d.bid = :bid')
            ->andWhere('d.money = :money')
            ->andWhere('d.type = :type')
            ->andWhere('d.year = :year')
            ->andWhere('r.bs != 0') // فقط ردیف‌هایی که bs صفر نیست
            ->setParameter('bid', $acc['bid'])
            ->setParameter('money', $acc['money'])
            ->setParameter('type', 'income')
            ->setParameter('year', $acc['year']);

        // درآمد امروز
        $todayIncome = (clone $qb)
            ->andWhere('d.date = :date')
            ->setParameter('date', $today)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // درآمد این هفته
        $weekIncome = (clone $qb)
            ->andWhere('d.date BETWEEN :start AND :end')
            ->setParameter('start', $weekStart)
            ->setParameter('end', $today)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // درآمد این ماه
        $monthIncome = (clone $qb)
            ->andWhere('d.date BETWEEN :start AND :end')
            ->setParameter('start', $monthStart)
            ->setParameter('end', $today)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // درآمد سال مالی
        $yearIncome = (clone $qb)
            ->andWhere('d.date BETWEEN :start AND :end')
            ->setParameter('start', $yearStart)
            ->setParameter('end', $yearEnd)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return $this->json([
            'today' => (int) $todayIncome,
            'week' => (int) $weekIncome,
            'month' => (int) $monthIncome,
            'year' => (int) $yearIncome,
        ]);
    }

    #[Route('/api/income/top-centers', name: 'app_income_top_centers', methods: ['GET'])]
    public function getTopIncomeCenters(
        Request $request,
        Access $access,
        Jdate $jdate,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // بررسی دسترسی کاربر
        $acc = $access->hasRole('income');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        // پارامترهای درخواست
        $period = $request->query->get('period', 'today'); // پیش‌فرض: امروز
        $limit = (int) $request->query->get('limit', 10); // پیش‌فرض: 10

        // تاریخ‌های شمسی برای امروز و ماه
        $today = $jdate->jdate('Y/m/d', time());
        $monthStart = $jdate->jdate('Y/m/01', time());

        // کوئری پایه
        $qb = $entityManager->createQueryBuilder()
            ->select('t.name AS center_name, SUM(COALESCE(r.bs, 0)) AS total_income')
            ->from('App\Entity\HesabdariDoc', 'd')
            ->join('d.hesabdariRows', 'r')
            ->join('r.ref', 't') // ارتباط با HesabdariTable
            ->where('d.bid = :bid')
            ->andWhere('d.money = :money')
            ->andWhere('d.type = :type')
            ->andWhere('d.year = :year')
            ->andWhere('r.bs != 0') // فقط ردیف‌هایی که bs صفر نیست
            ->groupBy('t.id, t.name')
            ->orderBy('total_income', 'DESC')
            ->setParameter('bid', $acc['bid'])
            ->setParameter('money', $acc['money'])
            ->setParameter('type', 'income')
            ->setParameter('year', $acc['year']);

        // اعمال فیلتر تاریخ فقط برای امروز و ماه
        if ($period === 'today') {
            $qb->andWhere('d.date = :date')
                ->setParameter('date', $today);
        } elseif ($period === 'month') {
            $qb->andWhere('d.date BETWEEN :start AND :end')
                ->setParameter('start', $monthStart)
                ->setParameter('end', $today);
        }
        // برای 'year' نیازی به شرط تاریخ نیست، چون year و type کافی است

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        $results = $qb->getQuery()->getResult();

        // آماده‌سازی داده‌ها برای نمودار
        $labels = [];
        $series = [];
        foreach ($results as $row) {
            $labels[] = $row['center_name'];
            $series[] = (int) $row['total_income'];
        }

        return $this->json([
            'labels' => $labels,
            'series' => $series,
        ]);
    }
}