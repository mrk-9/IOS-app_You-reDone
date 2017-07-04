//
//  UserModel.m
//  You're DONE
//
//  Created by Leo Lorenz on 2/12/16.
//  Copyright © 2016 Leo Lorenz. All rights reserved.
//

#import "UserModel.h"

@implementation UserModel

// 0 : first status, 1 : locked status, 2 : unlocked

- (id)init
{
    self = [super init];
    if (self) {
        self.username = @"";
        self.userpasscode = @"";
        self.userpassword = @"";
        self.userTime = [NSDate date];
        self.child_id = @"";
        self.userTime_amount = @"";
        self.status = 0;
    }
    return self;
}
@end
