<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Cashdesk;
use App\Entity\Commodity;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
use App\Entity\HesabdariDoc;
use App\Entity\Person;
use App\Entity\Salary;
use App\Service\Access;
use App\Service\Log;
use App\Service\Provider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
class DirectHesabdariDoc extends AbstractController
{
    #[Route('/api/hesabdari/direct/doc/create', name: 'create_hesabdari_doc_insert')]
    public function create(Log $log, Access $access, Request $request, Provider $provider, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('accounting');
        if (!$acc) {
            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز'], 403);
        }

        $prams = $request->getPayload()->all();

        $hesabdariDoc = new HesabdariDoc();
        $hesabdariDoc->setType('calc');
        $hesabdariDoc->setBid($acc['bid']);
        $hesabdariDoc->setSubmitter($this->getUser());
        $hesabdariDoc->setDes($prams['des']);
        $hesabdariDoc->setYear($acc['year']);
        $hesabdariDoc->setMoney($acc['money']);
        $hesabdariDoc->setDate($prams['date']);
        $hesabdariDoc->setCode($provider->getAccountingCode($acc['bid'], 'accounting'));
        $hesabdariDoc->setDateSubmit(time());
        
        //insert rows
        if (isset($prams['rows'])) {
            if (count($prams['rows']) < 2) {
                return new JsonResponse(['success' => false, 'message' => 'حداقل باید دو سطر در سند وجود داشته باشد'], 400);
            }
            $totalBs = 0;
            foreach ($prams['rows'] as $row) {
                $hesabdariRow = new HesabdariRow();
                $hesabdariRow->setDoc($hesabdariDoc);
                $hesabdariRow->setBs($row['bs']);
                $hesabdariRow->setBd($row['bd']);
                $hesabdariRow->setDes($row['des']);
                $hesabdariRow->setYear($acc['year']);
                $hesabdariRow->setRefData($row['detail']);
                $hesabdariRow->setBid($acc['bid']);
                $totalBs += floatval($row['bs']);
                //get ref
                $ref = $entityManager->getRepository(HesabdariTable::class)->find($row['ref']);
                if ($ref) {
                    if ($ref->getBid() == $acc['bid'] || $ref->getBid() == null) {
                        $hesabdariRow->setRef($ref);
                    } else {
                        return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به حساب'], 403);
                    }
                } else {
                    return new JsonResponse(['success' => false, 'message' => 'حساب مورد نظر یافت نشد'], 404);
                }

                if ($row['bankAccount']) {
                    $bankAccount = $entityManager->getRepository(BankAccount::class)->find($row['bankAccount']);
                    if ($bankAccount) {
                        if ($bankAccount->getBid() == $acc['bid']) {
                            $hesabdariRow->setBank($bankAccount);
                        } else {
                            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به حساب بانکی'], 403);
                        }
                    } else {
                        return new JsonResponse(['success' => false, 'message' => 'حساب بانکی مورد نظر یافت نشد'], 404);
                    }
                }
                if ($row['cashdesk']) {
                    $cashdesk = $entityManager->getRepository(Cashdesk::class)->find($row['cashdesk']);
                    if ($cashdesk) {
                        if ($cashdesk->getBid() == $acc['bid']) {
                            $hesabdariRow->setCashDesk($cashdesk);
                        } else {
                            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به صندوق'], 403);
                        }
                    } else {
                        return new JsonResponse(['success' => false, 'message' => 'صندوق مورد نظر یافت نشد'], 404);
                    }
                }

                if ($row['salary']) {
                    $salary = $entityManager->getRepository(Salary::class)->find($row['salary']);
                    if ($salary) {
                        if ($salary->getBid() == $acc['bid']) {
                            $hesabdariRow->setSalary($salary);
                        } else {
                            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به حقوق'], 403);
                        }
                    } else {
                        return new JsonResponse(['success' => false, 'message' => 'حقوق مورد نظر یافت نشد'], 404);
                    }
                }

                if ($row['person']) {
                    $person = $entityManager->getRepository(Person::class)->find($row['person']);
                    if ($person) {
                        if ($person->getBid() == $acc['bid']) {
                            $hesabdariRow->setPerson($person);
                        } else {
                            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به شخص'], 403);
                        }
                    } else {
                        return new JsonResponse(['success' => false, 'message' => 'شخص مورد نظر یافت نشد'], 404);
                    }
                }

                if ($row['commodity'] && $row['commodityCount']) {
                    if (!is_numeric($row['commodityCount']) || $row['commodityCount'] <= 0) {
                        return new JsonResponse(['success' => false, 'message' => 'تعداد کالا باید عددی مثبت باشد'], 400);
                    }
                    $commodity = $entityManager->getRepository(Commodity::class)->find($row['commodity']);
                    if ($commodity) {
                        if ($commodity->getBid() == $acc['bid']) {
                            $hesabdariRow->setCommodity($commodity);
                            $hesabdariRow->setCommdityCount($row['commodityCount']);
                        } else {
                            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به کالا'], 403);
                        }
                    } else {
                        return new JsonResponse(['success' => false, 'message' => 'کالای مورد نظر یافت نشد'], 404);
                    }
                }
                $entityManager->persist($hesabdariRow);
            }
            $hesabdariDoc->setAmount($totalBs);
        }
        $entityManager->persist($hesabdariDoc);
        $entityManager->flush();
        $log->insert('حسابداری', 'ایجاد سند حسابداری شماره ' . $hesabdariDoc->getCode(), $this->getUser(), $acc['bid'], $hesabdariDoc);
        return new JsonResponse(['success' => true, 'message' => 'سند با موفقیت ثبت شد', 'data' => ['id' => $hesabdariDoc->getId()]], 200);
    }

    #[Route('/api/hesabdari/direct/doc/update/{id}', name: 'update_hesabdari_doc_update')]
    public function update(Log $log, Access $access, Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('accounting');
        if (!$acc) {
            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز'], 403);
        }

        $hesabdariDoc = $entityManager->getRepository(HesabdariDoc::class)->find($id);
        if (!$hesabdariDoc) {
            return new JsonResponse(['success' => false, 'message' => 'سند مورد نظر یافت نشد'], 404);
        }

        if ($hesabdariDoc->getBid() !== $acc['bid']) {
            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به سند'], 403);
        }

        $prams = $request->getPayload()->all();

        $hesabdariDoc->setDes($prams['des']);
        $hesabdariDoc->setDate($prams['date']);

        // حذف ردیف‌های قبلی
        foreach ($hesabdariDoc->getHesabdariRows() as $row) {
            $entityManager->remove($row);
        }

        // اضافه کردن ردیف‌های جدید
        if (isset($prams['rows'])) {
            if (count($prams['rows']) < 2) {
                return new JsonResponse(['success' => false, 'message' => 'حداقل باید دو سطر در سند وجود داشته باشد'], 400);
            }
            $totalBs = 0;
            foreach ($prams['rows'] as $row) {
                $hesabdariRow = new HesabdariRow();
                $hesabdariRow->setDoc($hesabdariDoc);
                $hesabdariRow->setBs($row['bs']);
                $hesabdariRow->setBd($row['bd']);
                $hesabdariRow->setDes($row['des']);
                $hesabdariRow->setYear($acc['year']);
                $hesabdariRow->setRefData($row['detail']);
                $hesabdariRow->setBid($acc['bid']);
                $totalBs += floatval($row['bs']);
                //get ref
                $ref = $entityManager->getRepository(HesabdariTable::class)->find($row['ref']);
                if ($ref) {
                    if ($ref->getBid() == $acc['bid'] || $ref->getBid() == null) {
                        $hesabdariRow->setRef($ref);
                    } else {
                        return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به حساب'], 403);
                    }
                } else {
                    return new JsonResponse(['success' => false, 'message' => 'حساب مورد نظر یافت نشد'], 404);
                }

                if ($row['bankAccount']) {
                    $bankAccount = $entityManager->getRepository(BankAccount::class)->find($row['bankAccount']);
                    if ($bankAccount) {
                        if ($bankAccount->getBid() == $acc['bid']) {
                            $hesabdariRow->setBank($bankAccount);
                        } else {
                            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به حساب بانکی'], 403);
                        }
                    } else {
                        return new JsonResponse(['success' => false, 'message' => 'حساب بانکی مورد نظر یافت نشد'], 404);
                    }
                }

                if ($row['cashdesk']) {
                    $cashdesk = $entityManager->getRepository(Cashdesk::class)->find($row['cashdesk']);
                    if ($cashdesk) {
                        if ($cashdesk->getBid() == $acc['bid']) {
                            $hesabdariRow->setCashDesk($cashdesk);
                        } else {
                            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به صندوق'], 403);
                        }
                    } else {
                        return new JsonResponse(['success' => false, 'message' => 'صندوق مورد نظر یافت نشد'], 404);
                    }
                }

                if ($row['salary']) {
                    $salary = $entityManager->getRepository(Salary::class)->find($row['salary']);
                    if ($salary) {
                        if ($salary->getBid() == $acc['bid']) {
                            $hesabdariRow->setSalary($salary);
                        } else {
                            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به حقوق'], 403);
                        }
                    } else {
                        return new JsonResponse(['success' => false, 'message' => 'حقوق مورد نظر یافت نشد'], 404);
                    }
                }

                if ($row['person']) {
                    $person = $entityManager->getRepository(Person::class)->find($row['person']);
                    if ($person) {
                        if ($person->getBid() == $acc['bid']) {
                            $hesabdariRow->setPerson($person);
                        } else {
                            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به شخص'], 403);
                        }
                    } else {
                        return new JsonResponse(['success' => false, 'message' => 'شخص مورد نظر یافت نشد'], 404);
                    }
                }

                if ($row['commodity'] && $row['commodityCount']) {
                    if (!is_numeric($row['commodityCount']) || $row['commodityCount'] <= 0) {
                        return new JsonResponse(['success' => false, 'message' => 'تعداد کالا باید عددی مثبت باشد'], 400);
                    }
                    $commodity = $entityManager->getRepository(Commodity::class)->find($row['commodity']);
                    if ($commodity) {
                        if ($commodity->getBid() == $acc['bid']) {
                            $hesabdariRow->setCommodity($commodity);
                            $hesabdariRow->setCommdityCount($row['commodityCount']);
                        } else {
                            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به کالا'], 403);
                        }
                    } else {
                        return new JsonResponse(['success' => false, 'message' => 'کالای مورد نظر یافت نشد'], 404);
                    }
                }
                $entityManager->persist($hesabdariRow);
            }
            $hesabdariDoc->setAmount($totalBs);
        }

        $entityManager->flush();
        $log->insert('حسابداری', 'ویرایش سند حسابداری شماره ' . $hesabdariDoc->getCode(), $this->getUser(), $acc['bid'], $hesabdariDoc);
        return new JsonResponse(['success' => true, 'message' => 'سند با موفقیت ویرایش شد'], 200);
    }

    #[Route('/api/hesabdari/direct/doc/delete/{id}', name: 'delete_hesabdari_doc_delete')]
    public function delete(Log $log, Access $access, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('accounting');
        if (!$acc) {
            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز'], 403);
        }

        $hesabdariDoc = $entityManager->getRepository(HesabdariDoc::class)->find($id);
        if (!$hesabdariDoc) {
            return new JsonResponse(['success' => false, 'message' => 'سند مورد نظر یافت نشد'], 404);
        }
        if ($hesabdariDoc->getType() !== 'calc') {
            return new JsonResponse(['success' => false, 'message' => 'سند مورد نظر قابل حذف نیست'], 400);
        }
        if ($hesabdariDoc->getBid() !== $acc['bid']) {
            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به سند'], 403);
        }

        $entityManager->remove($hesabdariDoc);
        $entityManager->flush();
        $log->insert('حسابداری', 'حذف سند حسابداری شماره ' . $hesabdariDoc->getCode(), $this->getUser(), $acc['bid'], $hesabdariDoc);
        return new JsonResponse(['success' => true, 'message' => 'سند با موفقیت حذف شد'], 200);
    }

    #[Route('/api/hesabdari/direct/doc/get/{id}', name: 'get_hesabdari_doc_get')]
    public function get(Access $access, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('accounting');
        if (!$acc) {
            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز'], 403);
        }

        $hesabdariDoc = $entityManager->getRepository(HesabdariDoc::class)->find($id);
        if (!$hesabdariDoc) {
            return new JsonResponse(['success' => false, 'message' => 'سند مورد نظر یافت نشد'], 404);
        }

        if ($hesabdariDoc->getBid() !== $acc['bid']) {
            return new JsonResponse(['success' => false, 'message' => 'دسترسی غیرمجاز به سند'], 403);
        }

        $rows = [];
        foreach ($hesabdariDoc->getHesabdariRows() as $row) {
            $rowData = [
                'id' => $row->getId(),
                'ref' => [
                    'id' => $row->getRef()->getId(),
                    'name' => $row->getRef()->getName(),
                    'tableType' => $row->getRef()->getType()
                ],
                'bd' => $row->getBd(),
                'bs' => $row->getBs(),
                'des' => $row->getDes(),
                'detail' => $row->getRefData(),
                'bankAccount' => $row->getBank() ? $row->getBank()->getId() : null,
                'cashdesk' => $row->getCashDesk() ? $row->getCashDesk()->getId() : null,
                'salary' => $row->getSalary() ? $row->getSalary()->getId() : null,
                'commodity' => $row->getCommodity() ? $row->getCommodity()->getId() : null,
                'commodityCount' => $row->getCommdityCount(),
                'person' => $row->getPerson() ? $row->getPerson()->getId() : null
            ];
            $rows[] = $rowData;
        }

        $data = [
            'id' => $hesabdariDoc->getId(),
            'date' => $hesabdariDoc->getDate(),
            'des' => $hesabdariDoc->getDes(),
            'code' => $hesabdariDoc->getCode(),
            'rows' => $rows
        ];

        return new JsonResponse(['success' => true, 'data' => $data], 200);
    }
}
