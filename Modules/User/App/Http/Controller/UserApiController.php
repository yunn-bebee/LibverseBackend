<?php
    namespace Modules\User\App\Http\Controller;

    use Modules\User\App\Services\UserService;
    use Illuminate\Http\Request;
    use Illuminate\Routing\Controller;

    class UserApiController extends Controller
    {
        protected $userService;

        public function __construct(UserService $userService)
        {
            $this->userService = $userService;
        }

        public function index(Request $request)
        {
            try {
                [$noPagination, $perPage] = getNoPaginationPagPerPageFromRequest($request);

                $users = $this->userService->getAll(
                    $request->only(['search', 'role', 'status']),
                    !$noPagination,
                    $perPage
                );

                return apiResponse(
                    true,
                    'Users retrieved successfully',
                    $users ,
                    200,
                    [],
                    ['total' => $users->total()]
                );
            } catch (\Exception $e) {
                return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 500);
            }
        }

        public function show($id)
        {
            try {
                $user = $this->userService->get($id);

                if (!$user) {
                    return apiResponse(false, 'User not found', null, 404);
                }

                return apiResponse(true, 'User retrieved successfully', $user);
            } catch (\Exception $e) {
                return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 500);
            }
        }

        public function store(Request $request)
        {
            try {
                $result = $this->userService->save($request->all());

                if (!$result['success']) {
                    return apiResponse(
                        false,
                        $result['message'],
                        null,
                        400,
                        $result['errors']
                    );
                }

                return apiResponse(
                    true,
                    $result['message'],
                    $result['user'],
                    201
                );
            } catch (\Exception $e) {
                return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 500);
            }
        }

        public function update(Request $request, $id)
        {
            try {
                $result = $this->userService->update($id, $request->all());

                if (!$result['success']) {
                    return apiResponse(
                        false,
                        $result['message'],
                        null,
                        400,
                        $result['errors']
                    );
                }

                return apiResponse(
                    true,
                    $result['message'],
                    $result['user']
                );
            } catch (\Exception $e) {
                return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 500);
            }
        }

        public function destroy($id)
        {
            try {
                $this->userService->delete($id);
                return apiResponse(true, 'User deleted successfully');
            } catch (\Exception $e) {
                return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 500);
            }
        }

        public function ban($id)
        {
            try {
                $this->userService->banUser($id);
                return apiResponse(true, 'User banned successfully');
            } catch (\Exception $e) {
                return apiResponse(false, $e->getMessage(), null, $e->getCode() ?: 500);
            }
        }
    }
