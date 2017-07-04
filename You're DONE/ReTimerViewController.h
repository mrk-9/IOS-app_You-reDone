//
//  ReTimerViewController.h
//  You're DONE
//
//  Created by Leo Lorenz on 2/11/16.
//  Copyright © 2016 Leo Lorenz. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "Apimanager.h"

@interface ReTimerViewController : UIViewController
{
    Apimanager *apimanager;
}

@property (strong, nonatomic) NSString *nameTimerLabel;
@property (strong, nonatomic) NSString *passcodeTimerLabel;
@property (weak, nonatomic) IBOutlet UILabel *nameReTimerLabel;
@property (weak, nonatomic) IBOutlet UILabel *passcodeReTimerLabel;

@end
