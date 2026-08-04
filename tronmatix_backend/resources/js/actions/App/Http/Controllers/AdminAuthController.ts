import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminAuthController::showLogin
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
export const showLogin = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showLogin.url(options),
    method: 'get',
})

showLogin.definition = {
    methods: ["get","head"],
    url: '/dashboard/login',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminAuthController::showLogin
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
showLogin.url = (options?: RouteQueryOptions) => {
    return showLogin.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminAuthController::showLogin
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
showLogin.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showLogin.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminAuthController::showLogin
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
showLogin.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showLogin.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminAuthController::showLogin
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
    const showLoginForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showLogin.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminAuthController::showLogin
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
        showLoginForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showLogin.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminAuthController::showLogin
 * @see app/Http/Controllers/AdminAuthController.php:17
 * @route '/dashboard/login'
 */
        showLoginForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showLogin.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showLogin.form = showLoginForm
/**
* @see \App\Http\Controllers\AdminAuthController::login
 * @see app/Http/Controllers/AdminAuthController.php:28
 * @route '/dashboard/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: '/dashboard/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminAuthController::login
 * @see app/Http/Controllers/AdminAuthController.php:28
 * @route '/dashboard/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminAuthController::login
 * @see app/Http/Controllers/AdminAuthController.php:28
 * @route '/dashboard/login'
 */
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AdminAuthController::login
 * @see app/Http/Controllers/AdminAuthController.php:28
 * @route '/dashboard/login'
 */
    const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: login.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AdminAuthController::login
 * @see app/Http/Controllers/AdminAuthController.php:28
 * @route '/dashboard/login'
 */
        loginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: login.url(options),
            method: 'post',
        })
    
    login.form = loginForm
/**
* @see \App\Http\Controllers\AdminAuthController::showRegister
 * @see app/Http/Controllers/AdminAuthController.php:130
 * @route '/dashboard/register'
 */
export const showRegister = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showRegister.url(options),
    method: 'get',
})

showRegister.definition = {
    methods: ["get","head"],
    url: '/dashboard/register',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminAuthController::showRegister
 * @see app/Http/Controllers/AdminAuthController.php:130
 * @route '/dashboard/register'
 */
showRegister.url = (options?: RouteQueryOptions) => {
    return showRegister.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminAuthController::showRegister
 * @see app/Http/Controllers/AdminAuthController.php:130
 * @route '/dashboard/register'
 */
showRegister.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showRegister.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminAuthController::showRegister
 * @see app/Http/Controllers/AdminAuthController.php:130
 * @route '/dashboard/register'
 */
showRegister.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showRegister.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminAuthController::showRegister
 * @see app/Http/Controllers/AdminAuthController.php:130
 * @route '/dashboard/register'
 */
    const showRegisterForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showRegister.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminAuthController::showRegister
 * @see app/Http/Controllers/AdminAuthController.php:130
 * @route '/dashboard/register'
 */
        showRegisterForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showRegister.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminAuthController::showRegister
 * @see app/Http/Controllers/AdminAuthController.php:130
 * @route '/dashboard/register'
 */
        showRegisterForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showRegister.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showRegister.form = showRegisterForm
/**
* @see \App\Http\Controllers\AdminAuthController::register
 * @see app/Http/Controllers/AdminAuthController.php:143
 * @route '/dashboard/register'
 */
export const register = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: register.url(options),
    method: 'post',
})

register.definition = {
    methods: ["post"],
    url: '/dashboard/register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminAuthController::register
 * @see app/Http/Controllers/AdminAuthController.php:143
 * @route '/dashboard/register'
 */
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminAuthController::register
 * @see app/Http/Controllers/AdminAuthController.php:143
 * @route '/dashboard/register'
 */
register.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: register.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AdminAuthController::register
 * @see app/Http/Controllers/AdminAuthController.php:143
 * @route '/dashboard/register'
 */
    const registerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: register.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AdminAuthController::register
 * @see app/Http/Controllers/AdminAuthController.php:143
 * @route '/dashboard/register'
 */
        registerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: register.url(options),
            method: 'post',
        })
    
    register.form = registerForm
/**
* @see \App\Http\Controllers\AdminAuthController::logout
 * @see app/Http/Controllers/AdminAuthController.php:173
 * @route '/dashboard/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/dashboard/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminAuthController::logout
 * @see app/Http/Controllers/AdminAuthController.php:173
 * @route '/dashboard/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminAuthController::logout
 * @see app/Http/Controllers/AdminAuthController.php:173
 * @route '/dashboard/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AdminAuthController::logout
 * @see app/Http/Controllers/AdminAuthController.php:173
 * @route '/dashboard/logout'
 */
    const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: logout.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AdminAuthController::logout
 * @see app/Http/Controllers/AdminAuthController.php:173
 * @route '/dashboard/logout'
 */
        logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: logout.url(options),
            method: 'post',
        })
    
    logout.form = logoutForm
const AdminAuthController = { showLogin, login, showRegister, register, logout }

export default AdminAuthController