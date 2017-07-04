//
//  AppDelegate.h
//  You're DONE
//
//  Created by Leo Lorenz on 1/22/16.
//  Copyright © 2016 Leo Lorenz. All rights reserved.
//

#import <UIKit/UIKit.h>


@interface AppDelegate : UIResponder <UIApplicationDelegate>

@property (strong, nonatomic) UIWindow *window;

@property (readwrite) int selectedIndex;
@property (readwrite) NSInteger delhour;
@property (readwrite) NSInteger delminutes;
@property (readwrite) NSInteger delsecond;
@property (readwrite) NSMutableArray *arrayUsers;
@property (readwrite) BOOL unlockFlag;

@end

