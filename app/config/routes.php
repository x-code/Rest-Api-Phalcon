<?php
/**
 * @author Jete O'Keeffe
 * @version 1.0
 * @link http://docs.phalconphp.com/en/latest/reference/micro.html#defining-routes
 * @eg.
$routes[] = [
'method' => 'post',
'route' => '/api/update',
'handler' => 'myFunction'
];
 */
$routes[] = [
    'method'         => 'post',
    'route'          => '/common/ping',
    'handler'        => ['Controllers\CommonController', 'pongAction'],
    'authentication' => false,
];
// User endpoint
$routes[] = [
    'method'         => 'get',
    'route'          => '/hotel/list',
    'handler'        => ['Controllers\HotelController', 'listAction'],
    'authentication' => false,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/regdevice',
    'handler'        => ['Controllers\UserController', 'regDeviceAction'],
    'authentication' => false,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/register',
    'handler'        => ['Controllers\UserController', 'registrationAction'],
    'authentication' => false,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/login',
    'handler'        => ['Controllers\UserController', 'emailLoginAction'],
    'authentication' => false,
];
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/verification',
    'handler'        => ['Controllers\UserController', 'emailVerificationAction'],
    'authentication' => false,
];
// Profile Endpoint
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile',
    'handler'        => ['Controllers\ProfileController', 'profileListAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}',
    'handler'        => ['Controllers\ProfileController', 'profileViewAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile',
    'handler'        => ['Controllers\ProfileController', 'profileCreateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}',
    'handler'        => ['Controllers\ProfileController', 'profileUpdateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'delete',
    'route'          => '/user/profile/{profile_id}',
    'handler'        => ['Controllers\ProfileController', 'profileDeleteAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/photo',
    'handler'        => ['Controllers\ProfileController', 'profilePhotoSaveAction'],
    'authentication' => true,
];
// Address Endpoint
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}/address',
    'handler'        => ['Controllers\AddressController', 'addressListAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/address',
    'handler'        => ['Controllers\AddressController', 'addressCreateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/address/{address_id}',
    'handler'        => ['Controllers\AddressController', 'addressUpdateAction'],
    'authentication' => true,
];
// Indentification Endpoint
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}/identification',
    'handler'        => ['Controllers\IdentificationController', 'identificationListAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/identification',
    'handler'        => ['Controllers\IdentificationController', 'identificationCreateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}/identification/{identification_id}',
    'handler'        => ['Controllers\IdentificationController', 'identificationViewAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/identification/{identification_id}',
    'handler'        => ['Controllers\IdentificationController', 'identificationUpdateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'delete',
    'route'          => '/user/profile/{profile_id}/identification/{identification_id}',
    'handler'        => ['Controllers\IdentificationController', 'identificationDeleteAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/identification/{identification_id}/image',
    'handler'        => ['Controllers\IdentificationController', 'imageSaveAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'delete',
    'route'          => '/user/profile/{profile_id}/identification/{identification_id}/image/{resource_id}',
    'handler'        => ['Controllers\IdentificationController', 'imageDeleteAction'],
    'authentication' => true,
];
// Passport Endpoint
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}/passport',
    'handler'        => ['Controllers\PassportController', 'passportListAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/passport',
    'handler'        => ['Controllers\PassportController', 'passportCreateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}/passport/{passport_id}',
    'handler'        => ['Controllers\PassportController', 'passportViewAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/passport/{passport_id}',
    'handler'        => ['Controllers\PassportController', 'passportUpdateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'delete',
    'route'          => '/user/profile/{profile_id}/passport/{passport_id}',
    'handler'        => ['Controllers\PassportController', 'passportDeleteAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/passport/{passport_id}/image',
    'handler'        => ['Controllers\PassportController', 'imageSaveAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'delete',
    'route'          => '/user/profile/{profile_id}/passport/{passport_id}/image/{resource_id}',
    'handler'        => ['Controllers\PassportController', 'imageDeleteAction'],
    'authentication' => true,
];
// Reward Programme Endpoint
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}/reward',
    'handler'        => ['Controllers\RewardProgramController', 'rewardListAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/reward',
    'handler'        => ['Controllers\RewardProgramController', 'rewardCreateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}/reward/{reward_id}',
    'handler'        => ['Controllers\RewardProgramController', 'rewardViewAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/reward/{reward_id}',
    'handler'        => ['Controllers\RewardProgramController', 'rewardUpdateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'delete',
    'route'          => '/user/profile/{profile_id}/reward/{reward_id}',
    'handler'        => ['Controllers\RewardProgramController', 'rewardDeleteAction'],
    'authentication' => true,
];
// Important Contacts Endpoint
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}/contact',
    'handler'        => ['Controllers\ImportantContactController', 'importantContactListAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/contact',
    'handler'        => ['Controllers\ImportantContactController', 'importantContactCreateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}/contact/{contact_id}',
    'handler'        => ['Controllers\ImportantContactController', 'importantContactViewAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/contact/{contact_id}',
    'handler'        => ['Controllers\ImportantContactController', 'importantContactUpdateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'delete',
    'route'          => '/user/profile/{profile_id}/contact/{contact_id}',
    'handler'        => ['Controllers\ImportantContactController', 'importantContactDeleteAction'],
    'authentication' => true,
];
// Driver License Endpoint
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}/driver_license',
    'handler'        => ['Controllers\DriverLicenseController', 'driverLicenseListAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/driver_license',
    'handler'        => ['Controllers\DriverLicenseController', 'driverLicenseCreateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}/driver_license/{driver_license_id}',
    'handler'        => ['Controllers\DriverLicenseController', 'driverLicenseViewAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/driver_license/{driver_license_id}',
    'handler'        => ['Controllers\DriverLicenseController', 'driverLicenseUpdateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'delete',
    'route'          => '/user/profile/{profile_id}/driver_license/{driver_license_id}',
    'handler'        => ['Controllers\DriverLicenseController', 'driverLicenseDeleteAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/driver_license/{driver_license_id}/image',
    'handler'        => ['Controllers\DriverLicenseController', 'imageSaveAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'delete',
    'route'          => '/user/profile/{profile_id}/driver_license/{driver_license_id}/image/{resource_id}',
    'handler'        => ['Controllers\DriverLicenseController', 'imageDeleteAction'],
    'authentication' => true,
];
// Signature Endpoint
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}/signature',
    'handler'        => ['Controllers\SignatureController', 'signatureListAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'get',
    'route'          => '/user/profile/{profile_id}/signature/{signature_id}',
    'handler'        => ['Controllers\SignatureController', 'signatureViewAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/user/profile/{profile_id}/signature',
    'handler'        => ['Controllers\SignatureController', 'signatureCreateAction'],
    'authentication' => true,
];
$routes[] = [
    'method'         => 'delete',
    'route'          => '/user/profile/{profile_id}/signature/{signature_id}',
    'handler'        => ['Controllers\SignatureController', 'signatureDeleteAction'],
    'authentication' => true,
];
// DUMMY START
$routes[] = [
    'method'  => 'post',
    'route'   => '/ping',
    'handler' => ['Controllers\ExampleController', 'pingAction'],
];
$routes[] = [
    'method'  => 'get',
    'route'   => '/ping',
    'handler' => ['Controllers\ExampleController', 'getPingAction'],
];
$routes[] = [
    'method'  => 'post',
    'route'   => '/test/{id}',
    'handler' => ['Controllers\ExampleController', 'testAction'],
];
$routes[] = [
    'method'         => 'post',
    'route'          => '/skip/{name}',
    'handler'        => ['Controllers\ExampleController', 'skipAction'],
    'authentication' => false,
];
$routes[] = [
    'method'  => 'get',
    'route'   => '/get/listCountry',
    'handler' => ['Controllers\HotelController', 'listCountry'],
];
return $routes;
