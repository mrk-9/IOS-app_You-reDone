//
//  UserModel.h
//  You're DONE
//
//  Created by Leo Lorenz on 2/12/16.
//  Copyright © 2016 Leo Lorenz. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface UserModel : NSObject

@property (readwrite) NSString *username;
@property (readwrite) NSString *userpasscode;
@property (readwrite) NSDate *userTime;
@property (readwrite) NSString *userpassword;
@property (readwrite) NSString *child_id;
@property (readwrite) NSString* userTime_amount;
@property (readwrite) int status;

- (id)init;

@end
