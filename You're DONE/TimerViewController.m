//
//  TimerViewController.m
//  You're DONE
//
//  Created by Leo Lorenz on 1/27/16.
//  Copyright © 2016 Leo Lorenz. All rights reserved.
//

#import "TimerViewController.h"
#import "BaseroutAPI.h"
#import "AppDelegate.h"
#import "UserModel.h"
#import "SVProgressHUD.h"

@interface TimerViewController ()
{
    AppDelegate *delegate;
    NSTimer *timer;
}

@property (weak, nonatomic) IBOutlet UIButton *okTimerButton;
@property (weak, nonatomic) IBOutlet UIButton *cancelTimerButton;
@property (weak, nonatomic) IBOutlet UILabel *nameLabel;
@property (weak, nonatomic) IBOutlet UILabel *passcodeLabel;
@property (weak, nonatomic) IBOutlet UITextField *timeField;
@property (weak, nonatomic) IBOutlet UITextField *passwordTextField;
@property (weak, nonatomic) IBOutlet UITextField *reenterTextPassword;
@property (weak, nonatomic) IBOutlet UIScrollView *scrollView;
@property (weak, nonatomic) IBOutlet UILabel *matchLabel;
@property (weak, nonatomic) IBOutlet UILabel *timerLabel;

@property (strong, nonatomic) NSDate *fireDate;

@end

@implementation TimerViewController
{
    NSInteger hour;
    NSInteger minutes;
    NSInteger second;
    NSInteger amount_time;
}

- (void)viewDidLoad {
    [super viewDidLoad];
   
    delegate = [[UIApplication sharedApplication] delegate];
    
    self.okTimerButton.layer.borderWidth = 0;
    self.okTimerButton.layer.cornerRadius = 7.0;
    self.okTimerButton.layer.masksToBounds = YES;
    self.cancelTimerButton.layer.borderWidth = 0;
    self.cancelTimerButton.layer.cornerRadius = 7.0;
    self.cancelTimerButton.layer.masksToBounds = YES;
    
    // set the selecting name and passcode
    self.nameLabel.text = self.nameTimerLabel;
    self.passcodeLabel.text = self.passcodeTimerLabel;
    
    //date picker in keyboard
    UIDatePicker *datePicker = [[UIDatePicker alloc]init];
    [datePicker setDate:[NSDate date]];
    datePicker.datePickerMode = UIDatePickerModeTime;
    [datePicker addTarget:self action:@selector(updateTextField:) forControlEvents:UIControlEventValueChanged];
    NSLocale *locale = [[NSLocale alloc] initWithLocaleIdentifier:@"da_DK"];
    [datePicker setLocale:locale];
    [self.timeField setInputView:datePicker];
    
    
    //Show the Done on the keyboard begin
    UIToolbar* keyboardDoneButtonView = [[UIToolbar alloc] init];
    [keyboardDoneButtonView sizeToFit];
    UIBarButtonItem* doneButton = [[UIBarButtonItem alloc] initWithTitle:@"Done"
                                                                   style:UIBarButtonItemStylePlain target:self
                                                                  action:@selector(doneClicked:)];
    [keyboardDoneButtonView setItems:[NSArray arrayWithObjects:doneButton, nil]];
    
    _passwordTextField.inputAccessoryView = keyboardDoneButtonView;
    _reenterTextPassword.inputAccessoryView = keyboardDoneButtonView;
    
    //UITextFieldDelegate
    _reenterTextPassword.delegate = self;
    
    //Scroll the controls begin
    [[NSNotificationCenter defaultCenter] addObserver:self
                                             selector:@selector(keyboardDidShow:)
                                                 name:UIKeyboardDidShowNotification
                                               object:nil];
    [[NSNotificationCenter defaultCenter] addObserver:self
                                             selector:@selector(keyboardWillBeHidden:)
                                                 name:UIKeyboardWillHideNotification
                                               object:nil];
    [self.matchLabel setHidden:YES];
    
    [self.scrollView setDirectionalLockEnabled:YES];
}


- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

#pragma mark - Input
//update text field when date picker is dismissed
- (void)updateTextField:(UIDatePicker *)dtPicker{
    
    //access fireDate elsewhere
    self.fireDate = dtPicker.date;
    
    NSDateFormatter *timeFormatter = [[NSDateFormatter alloc] init];
    [timeFormatter setDateFormat:@"H:mm"];
    self.timeField.text = [timeFormatter stringFromDate:dtPicker.date];
    NSLog(@"Timer:%@",self.timeField);
    
    //display the time using Label
    NSCalendar * calendar = [NSCalendar currentCalendar];
    NSDateComponents * componenets = [calendar components:(NSCalendarUnitHour | NSCalendarUnitMinute | NSCalendarUnitSecond) fromDate:dtPicker.date];
    hour = [componenets hour];
    minutes = [componenets minute];
    amount_time = hour * 60 + minutes;
    second = [componenets second];
    [self.timerLabel setText:[NSString stringWithFormat:@"%02ld%@%02ld%@%02ld",(long)hour,@":",(long)minutes,@":",(long)second]];
    
//    delegate.delhour = hour;
//    delegate.delminutes = minutes;
//    delegate.delsecond = second;
}

//when press keyboard done button
- (IBAction)doneClicked:(id)sender
{
    [self.view endEditing:YES];
    
}

//Scroll the controls begin
- (BOOL)textFieldShouldReturn:(UITextField *)textField {
    
    [textField resignFirstResponder];
    return NO;
}

//control the scroll
- (void) keyboardDidShow:(NSNotification *)notification

{
    NSDictionary* info = [notification userInfo];
    CGRect kbRect = [[info objectForKey:UIKeyboardFrameBeginUserInfoKey] CGRectValue];
    kbRect = [self.view convertRect:kbRect fromView:nil];
    
    UIEdgeInsets contentInsets = UIEdgeInsetsMake(0.0, 0.0, kbRect.size.height, 0.0);
    self.scrollView.contentInset = contentInsets;
    self.scrollView.scrollIndicatorInsets = contentInsets;
    
    CGRect aRect = self.view.frame;
    aRect.size.height -= kbRect.size.height;
    if (!CGRectContainsPoint(aRect, self.passwordTextField.frame.origin) ) {
        
        CGRect sRect = self.passwordTextField.frame;
        sRect.size.height += 20;
        [self.scrollView scrollRectToVisible:sRect animated:YES];
        
    }
}

//Controll the scroll
- (void) keyboardWillBeHidden:(NSNotification *)notification
{
    UIEdgeInsets contentInsets = UIEdgeInsetsZero;
    self.scrollView.contentInset = contentInsets;
    self.scrollView.scrollIndicatorInsets = contentInsets;
}


//match the password and reenter password
- (BOOL)textField:(UITextField *)textField shouldChangeCharactersInRange:(NSRange)range replacementString:(NSString *)string    {
    
    if(_reenterTextPassword.text == NULL)
       [self.matchLabel setHidden:YES];
    
    if ([textField isEqual:_reenterTextPassword]) {
        NSString *recenterText = _reenterTextPassword.text;
        recenterText = [NSString stringWithFormat:@"%@%@",recenterText, string];
        
        if (recenterText == nil || recenterText.length == 1) {
            if (43 == nil || string.length == 0) {
                [self.matchLabel setHidden:YES];
                return YES;
            }
        }
        
        if ([recenterText isEqualToString:_passwordTextField.text]) {
            [self.matchLabel setHidden:YES];
        }else{
            [self.matchLabel setHidden:NO];
        }
    }
    return YES;
}

- (IBAction)okTimerButton:(id)sender {
    
    if([_passwordTextField.text length] < 5)
    {
        [[BaseroutAPI sharedInstance] MessageBox:@"Invalid Input" Message:@"Passwords must have at least 5 characters."];
        
    }else if ([_passwordTextField.text length] <= 0) {
        
        [[BaseroutAPI sharedInstance] MessageBox:@"Invalid Input" Message:@"Passwords is this."];
        
    }else if ([_reenterTextPassword.text length] <= 0) {
        
        [[BaseroutAPI sharedInstance] MessageBox:@"Invalid Input" Message:@"Please reenter passwords."];
        
    }else if ([_passwordTextField.text isEqualToString:_reenterTextPassword.text]) {
        
        
        UserModel * userModel = [delegate.arrayUsers objectAtIndex:delegate.selectedIndex];
        userModel.userpassword = _passwordTextField.text;
        userModel.userTime = self.fireDate;
        NSString *tempString = [NSString stringWithFormat: @"%ld", (long)amount_time];
        userModel.userTime_amount = tempString;
        userModel.status = 1;
        [delegate.arrayUsers replaceObjectAtIndex:delegate.selectedIndex withObject:userModel];
        
        //for setting up the verify image
        
        //run the clock for fire
        timer=[NSTimer scheduledTimerWithTimeInterval:1 target:self selector:@selector(timerFired) userInfo:nil repeats:YES];
        
        //Api Connection
        apimanager = [[Apimanager alloc] init];
        [SVProgressHUD showWithStatus:@"lock kid's phone..."];
        NSString *api_name;
        NSDictionary *params;
        api_name = @"/device/lock";
        params = @{@"child_id":userModel.child_id,@"platform":@"1",@"locktime":userModel.userTime_amount};
        
        [apimanager callAPI:api_name withParams:params success:^(NSData *data) {
            
            dispatch_sync(dispatch_get_main_queue(), ^{
                [SVProgressHUD dismiss];
                NSError *errorJson = nil;
                NSDictionary *dataDict = [NSJSONSerialization JSONObjectWithData:data options:kNilOptions error:&errorJson];
                
                if ([((NSDictionary *)dataDict)[@"status"] isEqualToString:@"OK"]){
                    NSLog(@"%@", dataDict);
                    
                    [[BaseroutAPI sharedInstance] MessageBox:@"Notice" Message:@"Please check the notification on your kid's phone."];
                    [self.navigationController popViewControllerAnimated:true];
                    
                }else if([((NSDictionary *)dataDict)[@"status"] isEqualToString:@"failed"])
                {
                    [[BaseroutAPI sharedInstance] MessageBox:@"Notice" Message:@"Sorry.failed.Please confirm if exist this child again."];
                    
                }
            });
        }
                      error:^(NSError *error){
                          
                          [SVProgressHUD dismiss];
                          [[BaseroutAPI sharedInstance] MessageBox:@"Notice" Message:@"Please check network status out!"];
                          
                      }];

        
        
       
        
    }else {
        
        [[BaseroutAPI sharedInstance] MessageBox:@"Invalid Input" Message:@"These passwords don't match."];
    }
    
}


- (IBAction)cancelTimerButton:(id)sender {
    
    [self.navigationController popViewControllerAnimated:true];
}

//Run the clock using Label
-(void)timerFired
{
    if((hour > 0 || minutes >= 0) && second >= 0)
    {
        if(second == 0)
        {
            if (minutes == 0)
            {
                hour -= 1;
                minutes = 59;
            }else if (minutes > 0)
            {
                minutes -= 1;
            }
            minutes -= 1;
            second = 59;
        }
        else if(second > 0)
        {
            second -= 1;
        }
        
        if(hour > -1)
        {
            delegate.delhour = hour;
            delegate.delminutes = minutes;
            delegate.delsecond = second;
        }
    }
    else
    {
        [timer invalidate];
    }
}


@end
