<?php
namespace App\Http\Middleware;
use App\Models\Studio;use App\Models\StudioDomain;use App\Support\TenantManager;use Closure;use Illuminate\Http\Request;use Illuminate\Support\Facades\Schema;
class ResolveStudioTenant{public function handle(Request $r,Closure $n){$s=$this->resolve($r)?:$this->fallback();app(TenantManager::class)->set($s);if($s){app()->instance('studio',$s);app()->instance('studio.id',$s->id);}return $n($r);}private function resolve(Request $r){