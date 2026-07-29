import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::show
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/dashboard/profile',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::show
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::show
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::show
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Dashboard\ProfileController::show
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::show
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::show
 * @see app/Http/Controllers/Dashboard/ProfileController.php:18
 * @route '/dashboard/profile'
 */
        showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post","put"],
    url: '/dashboard/profile',
} satisfies RouteDefinition<["post","put"]>

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
        updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(options),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::update
 * @see app/Http/Controllers/Dashboard/ProfileController.php:23
 * @route '/dashboard/profile'
 */
        updateForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::updatePassword
 * @see app/Http/Controllers/Dashboard/ProfileController.php:64
 * @route '/dashboard/profile/password'
 */
export const updatePassword = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatePassword.url(options),
    method: 'put',
})

updatePassword.definition = {
    methods: ["put"],
    url: '/dashboard/profile/password',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::updatePassword
 * @see app/Http/Controllers/Dashboard/ProfileController.php:64
 * @route '/dashboard/profile/password'
 */
updatePassword.url = (options?: RouteQueryOptions) => {
    return updatePassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::updatePassword
 * @see app/Http/Controllers/Dashboard/ProfileController.php:64
 * @route '/dashboard/profile/password'
 */
updatePassword.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatePassword.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\ProfileController::updatePassword
 * @see app/Http/Controllers/Dashboard/ProfileController.php:64
 * @route '/dashboard/profile/password'
 */
    const updatePasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updatePassword.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::updatePassword
 * @see app/Http/Controllers/Dashboard/ProfileController.php:64
 * @route '/dashboard/profile/password'
 */
        updatePasswordForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updatePassword.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updatePassword.form = updatePasswordForm
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::updateRole
 * @see app/Http/Controllers/Dashboard/ProfileController.php:84
 * @route '/dashboard/profile/role'
 */
export const updateRole = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateRole.url(options),
    method: 'put',
})

updateRole.definition = {
    methods: ["put"],
    url: '/dashboard/profile/role',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::updateRole
 * @see app/Http/Controllers/Dashboard/ProfileController.php:84
 * @route '/dashboard/profile/role'
 */
updateRole.url = (options?: RouteQueryOptions) => {
    return updateRole.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::updateRole
 * @see app/Http/Controllers/Dashboard/ProfileController.php:84
 * @route '/dashboard/profile/role'
 */
updateRole.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateRole.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Dashboard\ProfileController::updateRole
 * @see app/Http/Controllers/Dashboard/ProfileController.php:84
 * @route '/dashboard/profile/role'
 */
    const updateRoleForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateRole.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::updateRole
 * @see app/Http/Controllers/Dashboard/ProfileController.php:84
 * @route '/dashboard/profile/role'
 */
        updateRoleForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateRole.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateRole.form = updateRoleForm
/**
* @see \App\Http\Controllers\Dashboard\ProfileController::removeAvatar
 * @see app/Http/Controllers/Dashboard/ProfileController.php:52
 * @route '/dashboard/profile/avatar'
 */
export const removeAvatar = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: removeAvatar.url(options),
    method: 'delete',
})

removeAvatar.definition = {
    methods: ["delete"],
    url: '/dashboard/profile/avatar',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::removeAvatar
 * @see app/Http/Controllers/Dashboard/ProfileController.php:52
 * @route '/dashboard/profile/avatar'
 */
removeAvatar.url = (options?: RouteQueryOptions) => {
    return removeAvatar.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\ProfileController::removeAvatar
 * @see app/Http/Controllers/Dashboard/ProfileController.php:52
 * @route '/dashboard/profile/avatar'
 */
removeAvatar.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: removeAvatar.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Dashboard\ProfileController::removeAvatar
 * @see app/Http/Controllers/Dashboard/ProfileController.php:52
 * @route '/dashboard/profile/avatar'
 */
    const removeAvatarForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: removeAvatar.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\ProfileController::removeAvatar
 * @see app/Http/Controllers/Dashboard/ProfileController.php:52
 * @route '/dashboard/profile/avatar'
 */
        removeAvatarForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: removeAvatar.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    removeAvatar.form = removeAvatarForm
const ProfileController = { show, update, updatePassword, updateRole, removeAvatar }

export default ProfileController