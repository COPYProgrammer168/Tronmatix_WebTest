import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::post
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:32
 * @route '/dashboard/password/email'
 */
export const post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: post.url(options),
    method: 'post',
})

post.definition = {
    methods: ["post"],
    url: '/dashboard/password/email',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::post
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:32
 * @route '/dashboard/password/email'
 */
post.url = (options?: RouteQueryOptions) => {
    return post.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::post
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:32
 * @route '/dashboard/password/email'
 */
post.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: post.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::post
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:32
 * @route '/dashboard/password/email'
 */
    const postForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: post.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::post
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:32
 * @route '/dashboard/password/email'
 */
        postForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: post.url(options),
            method: 'post',
        })
    
    post.form = postForm
const email = {
    post: Object.assign(post, post),
}

export default email