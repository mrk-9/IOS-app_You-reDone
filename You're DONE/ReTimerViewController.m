//
//  ReTimerViewController.m
//  You're DONE
//
//  Created by Leo Lorenz on 2/11/16.
//  Copyright © 2016 Leo Lorenz. All rights reserved.
//

#import "ReTimerViewController.h"
#import "AppDelegate.h"
#import "UserModel.h"
#import "BaseroutAPI.h"
#import "SVProgressHUD.h"

@interface ReTimerViewController ()
{
    AppDelegate *delegate;
    NSTimer *timer;
}
@property (weak, nonatomic) IBOutlet UILabel *timeLabel;
@property (weak, nonatomic) IBOutlet UITextField *passwordTextField;
@property (weak, nonatomic) IBOutlet UIButton *reTimerOkButton;
@property (weak, nonatomic) IBOutlet UIButton *reTimerCancelButton;
@property (weak, nonatomic) IBOutlet UILabel *remainTimeLabel;
@property (weak, nonatomic) IBOutlet UIView *alertView;
@property (weak, nonatomic) IBOutlet UIButton *okButton;
@property (weak, nonatomic) IBOutlet UIButton *cancelButton;
@property (weak, nonatomic) IBOutlet UIView *unlockView;

@end


@implementation ReTimerViewController
{
    NSInteger reTimerHour;
    NSInteger reTimerMinutes;
    NSInteger reTimerSecond;
    UserModel * userModel;
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
    delegate = [[UIApplication sharedApplication] delegate];

    userModel = [delegate.arrayUsers objectAtIndex:delegate.selectedIndex];
    
    self.nameReTimerLabel.text = userModel.username;
    self.passcodeReTimerLabel.text = userModel.userpasscode;
    
//    self.nameReTimerLabel.text = self.nameTimerLabel;
//    self.passcodeReTimerLabel.text = self.passcodeTimerLabel;
    
    self.reTimerOkButton.layer.borderWidth =0;
    self.reTimerOkButton.layer.cornerRadius = 7.0;
    self.reTimerOkButton.layer.masksToBounds = YES;
    self.reTimerCancelButton.layer.borderWidth =0;
    self.reTimerCancelButton.layer.cornerRadius = 7.0;
    self.reTimerCancelButton.layer.masksToBounds = YES;
    self.unlockView.layer.borderWidth =0;
    self.unlockView.layer.cornerRadius = 7.0;
    self.unlockView.layer.masksToBounds = YES;
    self.okButton.layer.borderWidth =0;
    self.okButton.layer.cornerRadius = 7.0;
    self.okButton.layer.masksToBounds = YES;
    self.cancelButton.layer.borderWidth =0;
    self.cancelButton.layer.cornerRadius = 7.0;
    self.cancelButton.layer.masksToBounds = YES;
    
//    //hour,minutes,Second
//    reTimerHour = delegate.delhour;
//    reTimerMinutes = delegate.delminutes;
//    reTimerSecond = delegate.delsecond;
    
    //Hidden the AlertView
    [self.alertView setHidden:YES];
    
    //Set up the Time from userTime of UserModel
    NSDateFormatter *timeFormatter = [[NSDateFormatter alloc] init];
    [timeFormatter setDateFormat:@"H:mm"];
    NSString *ReTimerStr = [timeFormatter stringFromDate:userModel.userTime];
    
    //display the time using Label
    NSCalendar * calendar = [NSCalendar currentCalendar];
    NSDateComponents * componenets = [calendar components:(NSCalendarUnitHour | NSCalendarUnitMinute | NSCalendarUnitSecond) fromDate:userModel.userTime];
    reTimerHour = [componenets hour];
    reTimerMinutes = [componenets minute];
    reTimerSecond = [componenets second];
    
    //run the clock for fire
    timer=[NSTimer scheduledTimerWithTimeInterval:1 target:self selector:@selector(timerFired) userInfo:nil repeats:YES];
    
    //Show the Done on the keyboard begin
    UIToolbar* keyboardDoneButtonView = [[UIToolbar alloc] init];
    [keyboardDoneButtonView sizeToFit];
    UIBarButtonItem* doneButton = [[UIBarButtonItem alloc] initWithTitle:@"Done"
                                                                   style:UIBarButtonItemStylePlain target:self
                                                                  action:@selector(doneClicked:)];
    [keyboardDoneButtonView setItems:[NSArray arrayWithObjects:doneButton, nil]];
    
    _passwordTextField.inputAccessoryView = keyboardDoneButtonView;
    
}

- (void)viewDidAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
    
}


- (IBAction)reTimerOkButtonClicked:(id)sender {
    
    if(self.passwordTextField.text == userModel.userpassword)
    {
        [self.alertView setHidden:NO];
    }
    else if([self.passwordTextField.text length] == 0)
    {
        [[BaseroutAPI sharedInstance] MessageBox:@"Notice" Message:@"Please enter a password."];
    }else
        [[BaseroutAPI sharedInstance] MessageBox:@"Notice" Message:@"Password incorrect!"];
    
}
- (IBAction)reTimerCancelButtonClicked:(id)sender {
    
    
    [self.navigationController popViewControllerAnimated:true];
}

//Run the clock using Label
-(void)timerFired
{
    if((reTimerHour>0 || reTimerMinutes>=0) && reTimerSecond>=0)
    {
        if(reTimerSecond==0)
        {
            if (reTimerMinutes == 0)
            {
                reTimerHour -= 1;
                reTimerMinutes = 59;
            }else if (reTimerMinutes > 0)
            {
                reTimerMinutes -= 1;
            }
          
            reTimerSecond = 59;
        }
        else if(reTimerSecond > 0)
        {
            reTimerSecond -= 1;
        }
        
        if(reTimerHour > -1)
        {
             [self.timeLabel setText:[NSString stringWithFormat:@"%02d%@%02d%@%02d",reTimerHour,@":",reTimerMinutes,@":",reTimerSecond]];
            [self.remainTimeLabel setText:[NSString stringWithFormat:@"%02d%@%02d%@%02d",reTimerHour,@":",reTimerMinutes,@":",reTimerSecond]];
        }
    }
    else
    {
        [timer invalidate];
    }
}

//when press keyboard done button
- (IBAction)doneClicked:(id)sender
{
    [self.view endEditing:YES];
    
}
- (IBAction)reTimerOKButton:(id)sender {
    
    //Api Connection
    apimanager = [[Apimanager alloc] init];
    [self.alertView setHidden:YES];
    [SVProgressHUD showWithStatus:@"Unlock kid's phone..."];
    NSString *api_name;
    NSDictionary *params;
    api_name = @"/device/unlock";
    params = @{@"child_id":userModel.child_id,@"platform":@"1"};
    
    [apimanager callAPI:api_name withParams:params success:^(NSData *data) {
        
        dispatch_sync(dispatch_get_main_queue(), ^{
            
            [SVProgressHUD dismiss];
            NSError *errorJson = nil;
            NSDictionary *dataDict = [NSJSONSerialization JSONObjectWithData:data options:kNilOptions error:&errorJson];
            
            if ([((NSDictionary *)dataDict)[@"status"] isEqualToString:@"OK"]){
                NSLog(@"%@", dataDict);
                
                userModel.status = 0;
                [delegate.arrayUsers replaceObjectAtIndex:delegate.selectedIndex withObject:userModel];
                [self.navigationController popViewControllerAnimated:true];
                
            }else if([((NSDictionary *)dataDict)[@"status"] isEqualToString:@"failed"])
            {
                [[BaseroutAPI sharedInstance] MessageBox:@"Notice" Message:@"Sorry.failed.Please confirm if exist this child again."];
                
            }
        });
    }
                  error:^(NSError *error){
                      
                      [SVProgressHUD dismiss];
                      [self.alertView setHidden:YES];
                      [[BaseroutAPI sharedInstance] MessageBox:@"Notice" Message:@"Please check network status out!"];
                      
                  }];
    
   

    
}

- (IBAction)reTimerCancelButton:(id)sender {
    
    [self.alertView setHidden:YES];
}


@end
