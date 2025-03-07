<?php

namespace App\Controller\Plugins\Membership;

use App\Entity\User;
use App\Service\Jdate;
use App\Service\Extractor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

final class MembershipController extends AbstractController
{
    private $entityManager;
    private $jdate;
    private $extractor;

    public function __construct(EntityManagerInterface $entityManager, Jdate $jdate, Extractor $extractor)
    {
        $this->entityManager = $entityManager;
        $this->jdate = $jdate;
        $this->extractor = $extractor;
    }

    #[Route('/api/membership/stats', name: 'api_membership_stats', methods: ['GET'])]
    public function getMembershipStats(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse($this->extractor->operationFail('User not authenticated'), 401);
        }

        $userRepo = $this->entityManager->getRepository(User::class);
        $currentTime = time();

        // تاریخ‌های شمسی
        $todayStart = $this->jdate->jdate('Ymd', $currentTime, '', 'Asia/Tehran', 'en') . '000000';
        $todayStartTimestamp = $this->jdate->jmktime(0, 0, 0, substr($todayStart, 4, 2), substr($todayStart, 6, 2), substr($todayStart, 0, 4));
        $monthStart = $this->jdate->jdate('Ym01', $currentTime, '', 'Asia/Tehran', 'en') . '000000';
        $monthStartTimestamp = $this->jdate->jmktime(0, 0, 0, substr($monthStart, 4, 2), 1, substr($monthStart, 0, 4));
        $yearStart = $this->jdate->jdate('Y0101', $currentTime, '', 'Asia/Tehran', 'en') . '000000';
        $yearStartTimestamp = $this->jdate->jmktime(0, 0, 0, 1, 1, substr($yearStart, 0, 4));

        // آمار (فقط کاربران فعال)
        $stats = [
            'joinedToday' => (int) $userRepo->createQueryBuilder('u')
                ->where('u.invitedBy = :user')
                ->andWhere('CAST(u.dateRegister AS UNSIGNED) >= :start')
                ->andWhere('u.active = :active')
                ->setParameter('user', $user)
                ->setParameter('start', $todayStartTimestamp)
                ->setParameter('active', true)
                ->select('COUNT(u.id)')
                ->getQuery()
                ->getSingleScalarResult(),
            'joinedThisMonth' => (int) $userRepo->createQueryBuilder('u')
                ->where('u.invitedBy = :user')
                ->andWhere('CAST(u.dateRegister AS UNSIGNED) >= :start')
                ->andWhere('u.active = :active')
                ->setParameter('user', $user)
                ->setParameter('start', $monthStartTimestamp)
                ->setParameter('active', true)
                ->select('COUNT(u.id)')
                ->getQuery()
                ->getSingleScalarResult(),
            'joinedThisYear' => (int) $userRepo->createQueryBuilder('u')
                ->where('u.invitedBy = :user')
                ->andWhere('CAST(u.dateRegister AS UNSIGNED) >= :start')
                ->andWhere('u.active = :active')
                ->setParameter('user', $user)
                ->setParameter('start', $yearStartTimestamp)
                ->setParameter('active', true)
                ->select('COUNT(u.id)')
                ->getQuery()
                ->getSingleScalarResult(),
            'totalInvited' => (int) $userRepo->createQueryBuilder('u')
                ->where('u.invitedBy = :user')
                ->andWhere('u.active = :active')
                ->setParameter('user', $user)
                ->setParameter('active', true)
                ->select('COUNT(u.id)')
                ->getQuery()
                ->getSingleScalarResult(),
        ];

        // کاربران اخیر (همه کاربران، چه فعال و چه غیرفعال)
        $recentUsers = $userRepo->createQueryBuilder('u')
            ->where('u.invitedBy = :user')
            ->setParameter('user', $user)
            ->orderBy('u.dateRegister', 'DESC')
            ->setMaxResults(10)
            ->select('u.email, u.fullName, u.dateRegister, u.active')
            ->getQuery()
            ->getArrayResult();

        // چارت ۶ ماه گذشته (فقط کاربران فعال)
        $sixMonthsAgo = $this->jdate->jmktime(0, 0, 0, $this->jdate->jdate('m', $currentTime, '', 'Asia/Tehran', 'en') - 5, 1, $this->jdate->jdate('Y', $currentTime, '', 'Asia/Tehran', 'en'));
        $query = $userRepo->createQueryBuilder('u')
            ->where('u.invitedBy = :user')
            ->andWhere('CAST(u.dateRegister AS UNSIGNED) >= :start')
            ->andWhere('u.active = :active')
            ->setParameter('user', $user)
            ->setParameter('start', $sixMonthsAgo)
            ->setParameter('active', true)
            ->select('u.dateRegister')
            ->orderBy('u.dateRegister', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $monthlyData = [];
        foreach ($query as $row) {
            $date = (int) $row['dateRegister'];
            $jDate = $this->jdate->jdate('Ym', $date, '', 'Asia/Tehran', 'en');
            $monthlyData[$jDate] = ($monthlyData[$jDate] ?? 0) + 1;
        }

        $months = [];
        $users = [];
        $currentJMonth = (int) $this->jdate->jdate('m', $sixMonthsAgo, '', 'Asia/Tehran', 'en');
        $currentJYear = (int) $this->jdate->jdate('Y', $sixMonthsAgo, '', 'Asia/Tehran', 'en');

        for ($i = 0; $i < 6; $i++) {
            $monthName = $this->jdate->jdate('F', $this->jdate->jmktime(0, 0, 0, $currentJMonth, 1, $currentJYear));
            $months[] = $monthName;
            $key = sprintf('%04d%02d', $currentJYear, $currentJMonth);
            $users[] = $monthlyData[$key] ?? 0;

            $currentJMonth++;
            if ($currentJMonth > 12) {
                $currentJMonth = 1;
                $currentJYear++;
            }
        }

        $chart = [
            'months' => $months,
            'users' => $users,
        ];

        return new JsonResponse($this->extractor->operationSuccess([
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'chart' => $chart,
        ]));
    }
}