import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::post
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:45
 * @route '/dashboard/password/phone/verify'
 */
export const post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: post.url(options),
    method: 'post',
})

post.definition = {
    methods: ["post"],
    url: '/dashboard/password/phone/verify',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::post
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:45
 * @route '/dashboard/password/phone/verify'
 */
post.url = (options?: RouteQueryOptions) => {
    return post.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::post
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:45
 * @route '/dashboard/password/phone/verify'
 */
post.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: post.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::post
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:45
 * @route '/dashboard/password/phone/verify'
 */
    const postForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: post.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\PhoneOtpController::post
 * @see app/Http/Controllers/Dashboard/PhoneOtpController.php:45
 * @route '/dashboard/password/phone/verify'
 */
        postForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: post.url(options),
            method: 'post',
        })
    
    post.form = postForm
const verify = {
    post: Object.assign(post, post),
}

export default verify