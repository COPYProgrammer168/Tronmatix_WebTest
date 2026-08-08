import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AuthController::login
 * @see app/Http/Controllers/AuthController.php:66
 * @route '/api/auth/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: '/api/auth/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AuthController::login
 * @see app/Http/Controllers/AuthController.php:66
 * @route '/api/auth/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AuthController::login
 * @see app/Http/Controllers/AuthController.php:66
 * @route '/api/auth/login'
 */
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AuthController::login
 * @see app/Http/Controllers/AuthController.php:66
 * @route '/api/auth/login'
 */
    const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: login.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AuthController::login
 * @see app/Http/Controllers/AuthController.php:66
 * @route '/api/auth/login'
 */
        loginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: login.url(options),
            method: 'post',
        })
    
    login.form = loginForm
/**
* @see \App\Http\Controllers\AuthController::register
 * @see app/Http/Controllers/AuthController.php:22
 * @route '/api/auth/register'
 */
export const register = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: register.url(options),
    method: 'post',
})

register.definition = {
    methods: ["post"],
    url: '/api/auth/register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AuthController::register
 * @see app/Http/Controllers/AuthController.php:22
 * @route '/api/auth/register'
 */
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AuthController::register
 * @see app/Http/Controllers/AuthController.php:22
 * @route '/api/auth/register'
 */
register.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: register.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AuthController::register
 * @see app/Http/Controllers/AuthController.php:22
 * @route '/api/auth/register'
 */
    const registerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: register.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AuthController::register
 * @see app/Http/Controllers/AuthController.php:22
 * @route '/api/auth/register'
 */
        registerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: register.url(options),
            method: 'post',
        })
    
    register.form = registerForm
/**
* @see \App\Http\Controllers\AuthController::forgotPassword
 * @see app/Http/Controllers/AuthController.php:300
 * @route '/api/auth/forgot-password'
 */
export const forgotPassword = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: forgotPassword.url(options),
    method: 'post',
})

forgotPassword.definition = {
    methods: ["post"],
    url: '/api/auth/forgot-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AuthController::forgotPassword
 * @see app/Http/Controllers/AuthController.php:300
 * @route '/api/auth/forgot-password'
 */
forgotPassword.url = (options?: RouteQueryOptions) => {
    return forgotPassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AuthController::forgotPassword
 * @see app/Http/Controllers/AuthController.php:300
 * @route '/api/auth/forgot-password'
 */
forgotPassword.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: forgotPassword.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AuthController::forgotPassword
 * @see app/Http/Controllers/AuthController.php:300
 * @route '/api/auth/forgot-password'
 */
    const forgotPasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: forgotPassword.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AuthController::forgotPassword
 * @see app/Http/Controllers/AuthController.php:300
 * @route '/api/auth/forgot-password'
 */
        forgotPasswordForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: forgotPassword.url(options),
            method: 'post',
        })
    
    forgotPassword.form = forgotPasswordForm
/**
* @see \App\Http\Controllers\AuthController::resetPassword
 * @see app/Http/Controllers/AuthController.php:399
 * @route '/api/auth/reset-password'
 */
export const resetPassword = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetPassword.url(options),
    method: 'post',
})

resetPassword.definition = {
    methods: ["post"],
    url: '/api/auth/reset-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AuthController::resetPassword
 * @see app/Http/Controllers/AuthController.php:399
 * @route '/api/auth/reset-password'
 */
resetPassword.url = (options?: RouteQueryOptions) => {
    return resetPassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AuthController::resetPassword
 * @see app/Http/Controllers/AuthController.php:399
 * @route '/api/auth/reset-password'
 */
resetPassword.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetPassword.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AuthController::resetPassword
 * @see app/Http/Controllers/AuthController.php:399
 * @route '/api/auth/reset-password'
 */
    const resetPasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: resetPassword.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AuthController::resetPassword
 * @see app/Http/Controllers/AuthController.php:399
 * @route '/api/auth/reset-password'
 */
        resetPasswordForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: resetPassword.url(options),
            method: 'post',
        })
    
    resetPassword.form = resetPasswordForm
/**
* @see \App\Http\Controllers\AuthController::resetByPhone
 * @see app/Http/Controllers/AuthController.php:431
 * @route '/api/auth/reset-by-phone'
 */
export const resetByPhone = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetByPhone.url(options),
    method: 'post',
})

resetByPhone.definition = {
    methods: ["post"],
    url: '/api/auth/reset-by-phone',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AuthController::resetByPhone
 * @see app/Http/Controllers/AuthController.php:431
 * @route '/api/auth/reset-by-phone'
 */
resetByPhone.url = (options?: RouteQueryOptions) => {
    return resetByPhone.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AuthController::resetByPhone
 * @see app/Http/Controllers/AuthController.php:431
 * @route '/api/auth/reset-by-phone'
 */
resetByPhone.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetByPhone.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AuthController::resetByPhone
 * @see app/Http/Controllers/AuthController.php:431
 * @route '/api/auth/reset-by-phone'
 */
    const resetByPhoneForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: resetByPhone.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AuthController::resetByPhone
 * @see app/Http/Controllers/AuthController.php:431
 * @route '/api/auth/reset-by-phone'
 */
        resetByPhoneForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: resetByPhone.url(options),
            method: 'post',
        })
    
    resetByPhone.form = resetByPhoneForm
/**
* @see \App\Http\Controllers\AuthController::logout
 * @see app/Http/Controllers/AuthController.php:134
 * @route '/api/auth/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/api/auth/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AuthController::logout
 * @see app/Http/Controllers/AuthController.php:134
 * @route '/api/auth/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AuthController::logout
 * @see app/Http/Controllers/AuthController.php:134
 * @route '/api/auth/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AuthController::logout
 * @see app/Http/Controllers/AuthController.php:134
 * @route '/api/auth/logout'
 */
    const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: logout.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AuthController::logout
 * @see app/Http/Controllers/AuthController.php:134
 * @route '/api/auth/logout'
 */
        logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: logout.url(options),
            method: 'post',
        })
    
    logout.form = logoutForm
/**
* @see \App\Http\Controllers\AuthController::me
 * @see app/Http/Controllers/AuthController.php:141
 * @route '/api/auth/me'
 */
export const me = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: me.url(options),
    method: 'get',
})

me.definition = {
    methods: ["get","head"],
    url: '/api/auth/me',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AuthController::me
 * @see app/Http/Controllers/AuthController.php:141
 * @route '/api/auth/me'
 */
me.url = (options?: RouteQueryOptions) => {
    return me.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AuthController::me
 * @see app/Http/Controllers/AuthController.php:141
 * @route '/api/auth/me'
 */
me.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: me.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AuthController::me
 * @see app/Http/Controllers/AuthController.php:141
 * @route '/api/auth/me'
 */
me.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: me.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AuthController::me
 * @see app/Http/Controllers/AuthController.php:141
 * @route '/api/auth/me'
 */
    const meForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: me.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AuthController::me
 * @see app/Http/Controllers/AuthController.php:141
 * @route '/api/auth/me'
 */
        meForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: me.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AuthController::me
 * @see app/Http/Controllers/AuthController.php:141
 * @route '/api/auth/me'
 */
        meForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: me.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    me.form = meForm
/**
* @see \App\Http\Controllers\AuthController::portalMe
 * @see app/Http/Controllers/AuthController.php:154
 * @route '/api/portal/me'
 */
export const portalMe = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: portalMe.url(options),
    method: 'get',
})

portalMe.definition = {
    methods: ["get","head"],
    url: '/api/portal/me',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AuthController::portalMe
 * @see app/Http/Controllers/AuthController.php:154
 * @route '/api/portal/me'
 */
portalMe.url = (options?: RouteQueryOptions) => {
    return portalMe.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AuthController::portalMe
 * @see app/Http/Controllers/AuthController.php:154
 * @route '/api/portal/me'
 */
portalMe.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: portalMe.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AuthController::portalMe
 * @see app/Http/Controllers/AuthController.php:154
 * @route '/api/portal/me'
 */
portalMe.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: portalMe.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AuthController::portalMe
 * @see app/Http/Controllers/AuthController.php:154
 * @route '/api/portal/me'
 */
    const portalMeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: portalMe.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AuthController::portalMe
 * @see app/Http/Controllers/AuthController.php:154
 * @route '/api/portal/me'
 */
        portalMeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: portalMe.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AuthController::portalMe
 * @see app/Http/Controllers/AuthController.php:154
 * @route '/api/portal/me'
 */
        portalMeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: portalMe.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    portalMe.form = portalMeForm
const AuthController = { login, register, forgotPassword, resetPassword, resetByPhone, logout, me, portalMe }

export default AuthController