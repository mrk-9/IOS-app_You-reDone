//
//  TimerViewController.h
//  You're DONE
//
//  Created by Leo Lorenz on 1/27/16.
//  Copyright © 2016 Leo Lorenz. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "Apimanager.h"

@interface TimerViewController : UIViewController<UITextFieldDelegate>
{
    Apimanager *apimanager;
}

@property (strong, nonatomic) NSString *nameTimerLabel;
@property (strong, nonatomic) NSString *passcodeTimerLabel;

@end
