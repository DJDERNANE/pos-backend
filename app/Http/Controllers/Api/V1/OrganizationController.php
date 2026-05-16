<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\CreateOrganizationDTO;
use App\Http\Requests\Organization\OrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends ApiController
{
    public function __construct(
        private OrganizationService $organizationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $organizations = $this->organizationService->getAllForUser($request->user());

            return $this->successResponse(
                OrganizationResource::collection($organizations),
                'Organizations retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function store(OrganizationRequest $request): JsonResponse
    {
        try {
            $dto = CreateOrganizationDTO::fromArray($request->validated());
            $organization = $this->organizationService->create($dto, $request->user());

            return $this->successResponse(
                new OrganizationResource($organization),
                'Organization created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $organization = $this->organizationService->findById($id, $request->user());

            if (!$organization) {
                return $this->errorResponse('Organization not found', 404);
            }

            return $this->successResponse(
                new OrganizationResource($organization),
                'Organization retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function update(OrganizationRequest $request, string $id): JsonResponse
    {
        $dto = CreateOrganizationDTO::fromArray($request->validated());

        try {
            $organization = $this->organizationService->update($id, $dto, $request->user());
            return $this->successResponse(
                new OrganizationResource($organization),
                'Organization updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}
