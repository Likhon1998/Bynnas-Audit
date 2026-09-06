<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php
        /** @var \App\Models\User|null $user */
        $editing = (bool) $user;
        $selectedRole = old('role', $user?->roleKey() ?: ($suggestedRole ?? 'audit_officer'));
        $selectedShakhas = collect(old('shakha_ids', $user?->assignedShakhas?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
        $defaultEmployeeId = (int) old('employee_id', $user?->employee_id ?: ($prefillEmployeeId ?? 0));
        $createEmployee = (bool) old('create_employee', false);
        $prefillEmployee = $employees->firstWhere('id', $defaultEmployeeId);
    ?>

    <div class="px-4 py-4 lg:px-6">
        <div class="mb-4">
            <a href="<?php echo e(route('users.index')); ?>" class="text-[11px] font-medium text-[#2b579a] hover:underline">← Back to users</a>
            <h1 class="mt-1 text-[16px] font-semibold tracking-tight text-navy-900">
                <?php echo e($editing ? 'Edit user access' : 'Create employee login'); ?>

            </h1>
            <p class="mt-0.5 text-[12px] text-slate-500">Credentials + role + link employee · monthly visits auto-grant branches · optional extra shakhas</p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="mb-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-[12px] text-emerald-800"><?php echo e(session('status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form
            method="POST"
            action="<?php echo e($editing ? route('users.update', $user) : route('users.store')); ?>"
            class="mx-auto max-w-3xl space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
            x-data="{
                createEmployee: <?php echo e($createEmployee && ! $editing ? 'true' : 'false'); ?>,
                role: '<?php echo e($selectedRole); ?>',
                roleMenus: <?php echo e(Js::from(collect($roleCatalog)->mapWithKeys(fn ($r, $k) => [$k => $r['menus']]))); ?>

            }"
        >
            <?php echo csrf_field(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editing): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Name</label>
                    <input type="text" name="name" value="<?php echo e(old('name', $user?->name ?? $prefillEmployee?->name)); ?>" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" required>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-[11px] text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Email (login)</label>
                    <input type="email" name="email" value="<?php echo e(old('email', $user?->email ?? $prefillEmployee?->email)); ?>" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" required>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-[11px] text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                        Password <?php echo e($editing ? '(leave blank to keep)' : ''); ?>

                    </label>
                    <input type="password" name="password" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" <?php if (! ($editing)): ?> required <?php endif; ?> autocomplete="new-password">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-[11px] text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Confirm password</label>
                    <input type="password" name="password_confirmation" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" <?php if (! ($editing)): ?> required <?php endif; ?> autocomplete="new-password">
                </div>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Role access</label>
                <select name="role" x-model="role" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" required>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($role); ?>"><?php echo e($roleCatalog[$role]['label'] ?? $role); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
                <div class="mt-2 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-[11px] text-slate-600">
                    <p class="font-medium text-navy-900" x-text="(roleMenus[role] || []).length ? 'Can access:' : ''"></p>
                    <ul class="mt-1 list-inside list-disc text-slate-500">
                        <template x-for="item in (roleMenus[role] || [])" :key="item">
                            <li x-text="item"></li>
                        </template>
                    </ul>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $roleCatalog; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <p class="mt-1 text-[10px] text-slate-400" x-show="role === '<?php echo e($key); ?>'"><?php echo e($info['notes']); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-[11px] text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($editing)): ?>
                <div class="rounded-lg border border-slate-200 bg-slate-50/80 p-3">
                    <label class="flex items-center gap-2 text-[12px] font-medium text-slate-800">
                        <input type="checkbox" name="create_employee" value="1" class="rounded border-slate-300 text-[#2b579a]" x-model="createEmployee">
                        Also create organogram employee (name + email + position)
                    </label>
                    <p class="mt-1 text-[10px] text-slate-500">Use this when the person is not in the organogram yet. Otherwise link an existing employee below.</p>

                    <div class="mt-3" x-show="createEmployee" x-cloak>
                        <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Position</label>
                        <select name="position_id" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" :required="createEmployee">
                            <option value="">— Select position —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($position->id); ?>" <?php if((int) old('position_id') === $position->id): echo 'selected'; endif; ?>>
                                    <?php echo e($position->serial); ?>. <?php echo e($position->title); ?>

                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['position_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-[11px] text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div x-show="!createEmployee" x-cloak>
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Link employee</label>
                <select name="employee_id" class="h-9 w-full rounded-lg border-slate-200 text-[13px]" :disabled="createEmployee">
                    <option value="">— Not linked —</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $taken = $employee->user && (! $editing || $employee->user->id !== $user?->id);
                        ?>
                        <option
                            value="<?php echo e($employee->id); ?>"
                            <?php if($defaultEmployeeId === $employee->id): echo 'selected'; endif; ?>
                            <?php if($taken): echo 'disabled'; endif; ?>
                        >
                            <?php echo e($employee->name); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employee->position): ?> · <?php echo e($employee->position->title); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($taken): ?> (already has login) <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
                <p class="mt-1 text-[10px] text-slate-400">Links monthly visit assignments → accessible shakhas for officers</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-[11px] text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div>
                <label class="mb-1 flex items-start gap-2 text-[12px] text-slate-700">
                    <input type="checkbox" name="is_active" value="1" class="mt-0.5 rounded border-slate-300 text-[#2b579a]" <?php if(old('is_active', $user?->is_active ?? true)): echo 'checked'; endif; ?>>
                    <span>
                        <span class="font-semibold">Account active</span>
                        <span class="mt-0.5 block text-[10px] text-slate-400">Uncheck to deactivate — blocks login without deleting the account.</span>
                    </span>
                </label>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Extra shakha access (optional)</label>
                <p class="mb-2 text-[11px] text-slate-500">
                    Leave empty for normal officers — branch access comes automatically from <strong>Monthly Visits</strong> when they are allocated as visitor each month.
                    Use this list only to grant <strong>extra</strong> branches beyond those visit allocations.
                    Managers / Super Admin already see all.
                </p>
                <div class="max-h-56 overflow-y-auto rounded-lg border border-slate-200 p-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $shakhas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shakha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <label class="flex items-center gap-2 rounded px-1.5 py-1 text-[12px] hover:bg-slate-50">
                            <input
                                type="checkbox"
                                name="shakha_ids[]"
                                value="<?php echo e($shakha->id); ?>"
                                class="rounded border-slate-300 text-[#2b579a]"
                                <?php if(in_array((int) $shakha->id, $selectedShakhas, true)): echo 'checked'; endif; ?>
                            >
                            <span class="font-medium text-navy-900"><?php echo e($shakha->name); ?></span>
                            <span class="text-[10px] text-slate-400"><?php echo e($shakha->code); ?></span>
                        </label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['shakha_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-[11px] text-rose-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editing && $user->id !== auth()->id()): ?>
                    <button
                        type="submit"
                        form="delete-user"
                        class="text-[12px] font-medium text-rose-600 hover:underline"
                        onclick="return confirm('Permanently delete this login? The organogram employee (if linked) will be kept.')"
                    >Delete login</button>
                <?php else: ?>
                    <span></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button type="submit" class="inline-flex h-9 items-center rounded-lg bg-[#2b579a] px-4 text-[12px] font-semibold text-white hover:bg-[#204072]">
                    <?php echo e($editing ? 'Save changes' : 'Create login'); ?>

                </button>
            </div>
        </form>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editing && $user->id !== auth()->id()): ?>
            <form id="delete-user" method="POST" action="<?php echo e(route('users.destroy', $user)); ?>" class="hidden">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
            </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/users/form.blade.php ENDPATH**/ ?>